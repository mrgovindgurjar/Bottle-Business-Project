<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\BatchAllocation;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\InventoryService;


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
                ->lockForUpdate()->findOrFail($data['production_order_id']);

            if ($production->status !== 'completed') {
                throw ValidationException::withMessages(['production_order_id' => 'A batch can only be created from completed production.']);
            }

            $item = null;
            if (!empty($data['production_order_item_id'])) {
                $item = ProductionOrderItem::where('production_order_id', $production->id)
                    ->lockForUpdate()->findOrFail($data['production_order_item_id']);
            }

            if ($item && $item->product_id && (int) $item->product_id !== (int) $data['product_id']) {
                throw ValidationException::withMessages(['product_id' => 'Selected product does not belong to this production item.']);
            }

            $customerId = $data['customer_id'] ?? $production->customer_id;
            $designId = $data['design_id'] ?? ($item?->design_id ?: $production->design_id);
            $quantity = (float) $data['produced_quantity'];
            $rejected = (float) ($data['rejected_quantity'] ?? 0);

            if ($quantity + $rejected <= 0) {
                throw ValidationException::withMessages(['produced_quantity' => 'Batch quantity must be greater than zero.']);
            }

            $sourceProduced = $item ? (float) ($item->produced_quantity ?: $item->planned_quantity) : (float) $production->produced_quantity;
            if ($sourceProduced > 0) {
                $alreadyBatched = (float) Batch::where('production_order_id', $production->id)
                    ->where('production_order_item_id', $item?->id)
                    ->sum('produced_quantity');
                if ($quantity > max(0, $sourceProduced - $alreadyBatched) + 0.0001) {
                    throw ValidationException::withMessages(['produced_quantity' => 'Batch quantity exceeds the remaining production output.']);
                }
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

            return $batch->fresh(['productionOrder', 'productionOrderItem', 'product', 'design', 'customer', 'creator']);
        });
    }

    public function update(Batch $batch, array $data): Batch
    {
        return DB::transaction(function () use ($batch, $data) {
            if (in_array($batch->status, ['allocated', 'exhausted', 'cancelled'], true)) {
                throw ValidationException::withMessages(['batch' => 'This batch is already allocated/exhausted and cannot be edited.']);
            }
            $batch->update([
                'manufacturing_date' => $data['manufacturing_date'],
                'expiry_date' => $data['expiry_date'] ?? null,
                'quality_status' => $data['quality_status'],
                'quality_notes' => $data['quality_notes'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
            if ($batch->quality_status === 'passed' && $batch->status === 'quality_pending') {
                $batch->update(['status' => 'released', 'released_at' => now()]);
            }
            if (in_array($batch->quality_status, ['failed', 'hold'], true) && $batch->status === 'released') {
                $batch->update(['status' => 'quality_pending']);
            }
            return $batch->fresh(['productionOrder', 'product', 'design', 'customer']);
        });
    }

    public function release(Batch $batch, int $userId): Batch
    {
        return DB::transaction(function () use ($batch, $userId) {
            if ($batch->quality_status !== 'passed') {
                throw ValidationException::withMessages(['quality_status' => 'Batch must pass quality check before release.']);
            }
            if (in_array($batch->status, ['cancelled', 'blocked', 'exhausted'], true)) {
                throw ValidationException::withMessages(['batch' => 'This batch cannot be released in its current status.']);
            }
            $batch->update(['status' => $batch->allocatedQuantity() > 0 ? 'allocated' : 'released', 'released_by' => $userId, 'released_at' => now()]);
           $batch = $batch->fresh(['product']);
         $this->inventory->syncBatch($batch, $userId);

            return $batch->fresh();
        });
    }

    public function block(Batch $batch, string $reason): Batch
    {
        return DB::transaction(function () use ($batch, $reason) {
            if ($batch->allocatedQuantity() > 0) {
                throw ValidationException::withMessages(['batch' => 'A batch with allocations cannot be blocked. Handle its allocations first.']);
            }
            $batch->update(['status' => 'blocked', 'blocked_at' => now(), 'blocked_reason' => $reason]);
            return $batch->fresh();
        });
    }

    public function allocate(Batch $batch, array $data, int $userId): BatchAllocation
    {
        return DB::transaction(function () use ($batch, $data, $userId) {
            $batch = Batch::lockForUpdate()->findOrFail($batch->id);
            $quantity = (float) $data['quantity'];
            $available = $batch->calculatedAvailableQuantity();
            if (!$batch->canAllocate()) {
                throw ValidationException::withMessages(['batch' => 'This batch is not available for allocation.']);
            }
            if ($quantity <= 0 || $quantity > $available + 0.0001) {
                throw ValidationException::withMessages(['quantity' => 'Allocation quantity exceeds the available batch quantity.']);
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
            return $allocation->fresh(['batch', 'customer', 'order', 'allocator']);
        });
    }

    public function generateNumber(): string
    {
        $year = (int) now()->format('Y');
        $row = DB::table('batch_sequences')->where('year', $year)->lockForUpdate()->first();
        if (!$row) {
            DB::table('batch_sequences')->insert(['year' => $year, 'last_number' => 1, 'created_at' => now(), 'updated_at' => now()]);
            $number = 1;
        } else {
            $number = ((int) $row->last_number) + 1;
            DB::table('batch_sequences')->where('id', $row->id)->update(['last_number' => $number, 'updated_at' => now()]);
        }
        return 'BATCH-' . $year . '-' . str_pad((string) $number, 6, '0', STR_PAD_LEFT);
    }

    public function reverseAllocation(BatchAllocation $allocation, int $userId, string $reason): BatchAllocation
{
    return DB::transaction(function () use ($allocation, $userId, $reason) {
        $allocation = BatchAllocation::with('batch')->lockForUpdate()->findOrFail($allocation->id);
        if ($allocation->reversed_at) return $allocation;

        $allocation->update([
            'reversed_at' => now(),
            'reversed_by' => $userId,
            'reversal_reason' => $reason,
        ]);

        $batch = Batch::lockForUpdate()->findOrFail($allocation->batch_id);
        $available = $batch->calculatedAvailableQuantity();
        $batch->update(['status' => $available <= 0.0001 ? 'exhausted' : ($batch->quality_status === 'passed' ? 'released' : 'quality_pending')]);
        return $allocation->fresh(['batch','customer','order']);
    });
}

}
