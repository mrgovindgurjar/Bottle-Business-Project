<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\BatchAllocation;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BatchService
{
    public function __construct(private InventoryService $inventory) {}

    public function expireDueBatches(): void
    {
        Batch::whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', today())
            ->whereIn('status', ['quality_pending', 'released', 'allocated'])
            ->update(['status' => 'expired']);
    }

    public function create(array $data, int $userId): Batch
    {
        return DB::transaction(function () use ($data, $userId) {
            $production = ProductionOrder::with(['items', 'order', 'customer', 'design'])
                ->lockForUpdate()
                ->findOrFail($data['production_order_id']);

            if ($production->status !== 'completed') {
                throw ValidationException::withMessages(['production_order_id' => 'A batch can only be created from completed production.']);
            }

            if ($production->items->count() > 1 && empty($data['production_order_item_id'])) {
                throw ValidationException::withMessages(['production_order_item_id' => 'Select the production item when a production job contains multiple products.']);
            }

            $item = null;
            if (!empty($data['production_order_item_id'])) {
                $item = ProductionOrderItem::where('production_order_id', $production->id)
                    ->lockForUpdate()
                    ->findOrFail($data['production_order_item_id']);
            }

            if ($item && $item->product_id && (int) $item->product_id !== (int) $data['product_id']) {
                throw ValidationException::withMessages(['product_id' => 'Selected product does not belong to this production item.']);
            }

            if ($production->items->isNotEmpty() && !$item) {
                $item = $production->items->first();
            }

            $customerId = $data['customer_id'] ?? $production->customer_id;
            $designId = $data['design_id'] ?? ($item?->design_id ?: $production->design_id);
            $quantity = (float) $data['produced_quantity'];
            $rejected = (float) ($data['rejected_quantity'] ?? 0);

            if ($quantity <= 0) {
                throw ValidationException::withMessages(['produced_quantity' => 'Batch quantity must be greater than zero.']);
            }
            if ($rejected < 0 || $quantity + $rejected <= 0) {
                throw ValidationException::withMessages(['rejected_quantity' => 'Rejected quantity is invalid.']);
            }

            if ($designId) {
                $design = \App\Models\DesignRequest::find($designId);
                if (!$design || $design->status !== 'approved') {
                    throw ValidationException::withMessages(['design_id' => 'Only an approved design can be attached to a finished batch.']);
                }
            }

            $sourceProduced = $item
                ? (float) ($item->produced_quantity ?: $item->planned_quantity)
                : (float) $production->produced_quantity;

            if ($sourceProduced <= 0) {
                throw ValidationException::withMessages(['produced_quantity' => 'No completed production quantity is available for this batch.']);
            }

            $alreadyBatched = (float) Batch::where('production_order_id', $production->id)
                ->where('production_order_item_id', $item?->id)
                ->sum('produced_quantity');

            if ($quantity > max(0, $sourceProduced - $alreadyBatched) + 0.0001) {
                throw ValidationException::withMessages(['produced_quantity' => 'Batch quantity exceeds the remaining production output.']);
            }

            $batch = Batch::create([
                'batch_number' => $this->generateNumber(),
                'production_order_id' => $production->id,
                'production_order_item_id' => $item?->id,
                'product_id' => $data['product_id'],
                'design_id' => $designId,
                'customer_id' => $customerId,
                'manufacturing_date' => $data['manufacturing_date'],
                'expiry_date' => $data['expiry_date'] ?? null,
                'produced_quantity' => $quantity,
                'rejected_quantity' => $rejected,
                'available_quantity' => $quantity,
                'status' => $data['quality_status'] === 'passed' ? 'released' : 'quality_pending',
                'quality_status' => $data['quality_status'],
                'quality_notes' => $data['quality_notes'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
                'released_by' => $data['quality_status'] === 'passed' ? $userId : null,
                'released_at' => $data['quality_status'] === 'passed' ? now() : null,
            ]);

            if ($batch->quality_status === 'passed') {
                $this->inventory->syncBatch($batch, $userId);
            }

            $this->audit('created', $batch, null, [
                'batch_number' => $batch->batch_number,
                'production_order_id' => $batch->production_order_id,
                'quantity' => $quantity,
            ], $userId, 'Batch created');

            return $batch->fresh(['productionOrder', 'productionOrderItem', 'product', 'design', 'customer', 'creator']);
        });
    }

    public function update(Batch $batch, array $data, ?int $userId = null): Batch
    {
        return DB::transaction(function () use ($batch, $data, $userId) {
            $batch = Batch::lockForUpdate()->findOrFail($batch->id);

            if (in_array($batch->status, ['allocated', 'exhausted', 'cancelled', 'expired'], true)) {
                throw ValidationException::withMessages(['batch' => 'This batch can no longer be edited.']);
            }

            if ($batch->status === 'released' && ($data['quality_status'] ?? $batch->quality_status) !== 'passed') {
                throw ValidationException::withMessages(['quality_status' => 'A released batch cannot be downgraded. Block the batch instead if there is a quality issue.']);
            }

            $old = $batch->only(['manufacturing_date', 'expiry_date', 'quality_status', 'quality_notes', 'notes', 'status']);
            $batch->update([
                'manufacturing_date' => $data['manufacturing_date'],
                'expiry_date' => $data['expiry_date'] ?? null,
                'quality_status' => $data['quality_status'],
                'quality_notes' => $data['quality_notes'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            if ($batch->quality_status === 'passed' && $batch->status === 'quality_pending') {
                $batch->update(['status' => 'released', 'released_at' => now(), 'released_by' => $userId]);
                $this->inventory->syncBatch($batch->fresh(['product']), $userId ?? auth()->id());
            }

            if (in_array($batch->quality_status, ['failed', 'hold'], true) && $batch->status === 'quality_pending') {
                $batch->update(['status' => 'quality_pending']);
            }

            $updated = $batch->fresh(['productionOrder', 'product', 'design', 'customer']);
            if ($userId) {
                $this->audit('updated', $updated, $old, $updated->only(array_keys($old)), $userId, 'Batch updated');
            }

            return $updated;
        });
    }

    public function release(Batch $batch, int $userId): Batch
    {
        return DB::transaction(function () use ($batch, $userId) {
            $batch = Batch::lockForUpdate()->findOrFail($batch->id);

            if ($batch->quality_status !== 'passed') {
                throw ValidationException::withMessages(['quality_status' => 'Batch must pass quality check before release.']);
            }
            if (in_array($batch->status, ['cancelled', 'blocked', 'exhausted', 'expired'], true)) {
                throw ValidationException::withMessages(['batch' => 'This batch cannot be released in its current status.']);
            }

            $batch->update([
                'status' => $batch->allocatedQuantity() > 0 ? 'allocated' : 'released',
                'released_by' => $userId,
                'released_at' => now(),
            ]);

            $this->inventory->syncBatch($batch->fresh(['product']), $userId);
            $updated = $batch->fresh();
            $this->audit('released', $updated, null, ['status' => $updated->status], $userId, 'Batch released');

            return $updated;
        });
    }

    public function block(Batch $batch, string $reason, ?int $userId = null): Batch
    {
        return DB::transaction(function () use ($batch, $reason, $userId) {
            $batch = Batch::lockForUpdate()->findOrFail($batch->id);

            if ($batch->allocatedQuantity() > 0) {
                throw ValidationException::withMessages(['batch' => 'A batch with allocations cannot be blocked. Handle its allocations first.']);
            }
            if (in_array($batch->status, ['cancelled', 'exhausted', 'expired'], true)) {
                throw ValidationException::withMessages(['batch' => 'This batch cannot be blocked in its current status.']);
            }

            $batch->update(['status' => 'blocked', 'blocked_at' => now(), 'blocked_reason' => $reason]);

            if ($userId) {
                $this->audit('blocked', $batch, null, ['status' => 'blocked', 'reason' => $reason], $userId, 'Batch blocked');
            }

            return $batch->fresh();
        });
    }

    public function allocate(Batch $batch, array $data, int $userId): BatchAllocation
    {
        return DB::transaction(function () use ($batch, $data, $userId) {
            $batch = Batch::lockForUpdate()->findOrFail($batch->id);
            $quantity = (float) $data['quantity'];

            if (!$batch->canAllocate()) {
                throw ValidationException::withMessages(['batch' => 'This batch is not available for allocation.']);
            }
            if ($quantity <= 0 || $quantity > $batch->calculatedAvailableQuantity() + 0.0001) {
                throw ValidationException::withMessages(['quantity' => 'Allocation quantity exceeds the available batch quantity.']);
            }

            if (!empty($data['order_id'])) {
                $order = Order::findOrFail($data['order_id']);
                if ((int) $order->customer_id !== (int) $data['customer_id']) {
                    throw ValidationException::withMessages(['order_id' => 'The selected order does not belong to the selected customer.']);
                }
                if ($order->status === 'cancelled') {
                    throw ValidationException::withMessages(['order_id' => 'Cancelled orders cannot receive batch allocations.']);
                }
            }

            if (!empty($data['delivery_id'])) {
                $delivery = Delivery::findOrFail($data['delivery_id']);
                if ((int) $delivery->customer_id !== (int) $data['customer_id']) {
                    throw ValidationException::withMessages(['delivery_id' => 'The selected delivery does not belong to the selected customer.']);
                }
                if ((int) $delivery->order_id !== (int) ($data['order_id'] ?? 0)) {
                    throw ValidationException::withMessages(['delivery_id' => 'The selected delivery does not belong to the selected order.']);
                }
            }

            $allocation = BatchAllocation::create([
                'batch_id' => $batch->id,
                'customer_id' => $data['customer_id'],
                'order_id' => $data['order_id'] ?? null,
                'delivery_id' => $data['delivery_id'] ?? null,
                'quantity' => $quantity,
                'allocated_at' => now(),
                'allocated_by' => $userId,
                'notes' => $data['notes'] ?? null,
            ]);

            $remaining = $batch->calculatedAvailableQuantity();
            $batch->update(['status' => $remaining <= 0.0001 ? 'exhausted' : 'allocated']);

            $this->audit('allocated', $allocation, null, [
                'batch_id' => $batch->id,
                'customer_id' => $allocation->customer_id,
                'quantity' => $quantity,
            ], $userId, 'Batch quantity allocated');

            return $allocation->fresh(['batch', 'customer', 'order', 'allocator']);
        });
    }

    public function reverseAllocation(BatchAllocation $allocation, int $userId, string $reason): BatchAllocation
    {
        return DB::transaction(function () use ($allocation, $userId, $reason) {
            $allocation = BatchAllocation::lockForUpdate()->findOrFail($allocation->id);
            if ($allocation->reversed_at) return $allocation->fresh(['batch', 'customer', 'order']);

            $batch = Batch::lockForUpdate()->findOrFail($allocation->batch_id);

            $allocation->update([
                'reversed_at' => now(),
                'reversed_by' => $userId,
                'reversal_reason' => $reason,
            ]);

            $available = $batch->calculatedAvailableQuantity();
            $batch->update([
                'status' => $available <= 0.0001
                    ? 'exhausted'
                    : ($batch->quality_status === 'passed' ? 'released' : 'quality_pending'),
            ]);

            $this->audit('allocation_reversed', $allocation, null, [
                'batch_id' => $batch->id,
                'quantity' => $allocation->quantity,
                'reason' => $reason,
            ], $userId, 'Batch allocation reversed');

            return $allocation->fresh(['batch', 'customer', 'order']);
        });
    }

    public function generateNumber(): string
    {
        $year = (int) now()->format('Y');
        $row = DB::table('batch_sequences')->where('year', $year)->lockForUpdate()->first();

        if (!$row) {
            DB::table('batch_sequences')->insert([
                'year' => $year,
                'last_number' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $number = 1;
        } else {
            $number = ((int) $row->last_number) + 1;
            DB::table('batch_sequences')->where('id', $row->id)->update(['last_number' => $number, 'updated_at' => now()]);
        }

        return 'BATCH-'.$year.'-'.str_pad((string) $number, 6, '0', STR_PAD_LEFT);
    }

    private function audit(string $event, object $auditable, ?array $old, ?array $new, int $userId, string $description): void
    {
        if (!class_exists(\App\Models\AuditLog::class)) return;
        \App\Models\AuditLog::record($event, $auditable, $old, $new, $description, $userId);
    }
}
