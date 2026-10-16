<?php

namespace App\Services;

use App\Models\DesignRequest;
use App\Models\Order;
use App\Models\ProductionOrder;
use App\Models\ProductionStep;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionService
{
    public function createFromOrder(Order $order, int $userId): ProductionOrder
    {
        return DB::transaction(function () use ($order, $userId) {
            $order = Order::query()->with(['items', 'customer', 'design'])->lockForUpdate()->findOrFail($order->id);

            if (!in_array($order->status, ['confirmed', 'in_production', 'on_hold'], true)) {
                throw ValidationException::withMessages(['order' => 'Only confirmed/on-hold orders can enter production.']);
            }

            if ($order->design_id) {
                $design = DesignRequest::find($order->design_id);
                if (!$design || $design->status !== 'approved') {
                    throw ValidationException::withMessages(['order' => 'The order design must be approved before production can start.']);
                }
            }

            $existing = ProductionOrder::where('order_id', $order->id)
                ->whereNotIn('status', ['cancelled'])
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing->fresh(['items', 'steps', 'order', 'customer', 'design']);
            }

            if ($order->items->isEmpty()) {
                throw ValidationException::withMessages(['order' => 'An order must contain at least one item before production.']);
            }

            $planned = (float) $order->items->sum(fn ($i) => (float) $i->quantity);
            if ($planned <= 0) {
                throw ValidationException::withMessages(['order' => 'Production quantity must be greater than zero.']);
            }

            $production = ProductionOrder::create([
                'production_number' => $this->generateNumber(),
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'design_id' => $order->design_id,
                'planned_quantity' => $planned,
                'status' => 'pending',
                'created_by' => $userId,
            ]);

            foreach ($order->items as $i => $item) {
                $production->items()->create([
                    'order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'design_id' => $item->design_id ?: $order->design_id,
                    'description' => $item->description,
                    'planned_quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'sort_order' => $i,
                    'metadata' => $item->metadata,
                ]);
            }

            foreach ($this->defaultSteps() as $i => $step) {
                $production->steps()->create([
                    'code' => $step[0],
                    'name' => $step[1],
                    'sort_order' => $i,
                    'status' => 'pending',
                ]);
            }

            if ($order->status !== 'in_production') {
                $order->update(['status' => 'in_production']);
            }

            $this->audit('created', $production, null, [
                'production_number' => $production->production_number,
                'order_id' => $production->order_id,
            ], $userId, 'Production job created');

            return $production->fresh(['items.product', 'steps', 'order', 'customer', 'design']);
        });
    }

    public function update(ProductionOrder $production, array $data, ?int $userId = null): ProductionOrder
    {
        return DB::transaction(function () use ($production, $data, $userId) {
            $locked = ProductionOrder::lockForUpdate()->findOrFail($production->id);

            if (!$locked->isActive()) {
                throw ValidationException::withMessages(['production' => 'Completed/cancelled production cannot be edited.']);
            }

            $old = $locked->only(['scheduled_date', 'priority', 'assigned_to', 'notes', 'internal_notes', 'quality_status', 'quality_notes']);
            $locked->update($data);
            $updated = $locked->fresh(['items.product', 'steps', 'order', 'customer', 'design', 'assignee']);

            if ($userId) {
                $this->audit('updated', $updated, $old, $updated->only(array_keys($old)), $userId, 'Production job updated');
            }

            return $updated;
        });
    }

    public function changeStatus(ProductionOrder $production, string $status, int $userId): ProductionOrder
    {
        return DB::transaction(function () use ($production, $status, $userId) {
            $p = ProductionOrder::with(['order', 'steps'])->lockForUpdate()->findOrFail($production->id);

            $allowed = [
                'pending' => ['scheduled', 'in_progress', 'on_hold', 'cancelled'],
                'scheduled' => ['in_progress', 'on_hold', 'cancelled'],
                'in_progress' => ['quality_check', 'on_hold', 'cancelled'],
                'quality_check' => ['completed', 'in_progress', 'on_hold'],
                'on_hold' => ['scheduled', 'in_progress', 'cancelled'],
                'completed' => [],
                'cancelled' => [],
            ];

            if (!in_array($status, $allowed[$p->status] ?? [], true)) {
                throw ValidationException::withMessages([
                    'production' => "Cannot move production from {$p->status} to {$status}.",
                ]);
            }

            $oldStatus = $p->status;
            $payload = ['status' => $status];

            if ($status === 'in_progress' && !$p->started_at) {
                $payload['started_at'] = now();
            }

            if ($status === 'completed') {
                $this->assertCompletable($p);
                $payload['completed_at'] = now();
                $payload['completed_by'] = $userId;
                $payload['quality_status'] = 'passed';
            }

            if ($status === 'cancelled') {
                $payload['cancelled_at'] = now();
            }

            $p->update($payload);

            if ($status === 'completed' && $p->order && in_array($p->order->status, ['in_production', 'confirmed'], true)) {
                $p->order->update(['status' => 'ready']);
            }

            if ($status === 'cancelled' && $p->order && $p->order->status === 'in_production') {
                $p->order->update(['status' => 'on_hold']);
            }

            $updated = $p->fresh(['steps', 'items', 'order']);
            $this->audit('status_changed', $updated, ['status' => $oldStatus], ['status' => $status], $userId, 'Production status changed');

            return $updated;
        });
    }

    public function updateProgress(ProductionOrder $production, array $data, ?int $userId = null): ProductionOrder
    {
        return DB::transaction(function () use ($production, $data, $userId) {
            $p = ProductionOrder::lockForUpdate()->findOrFail($production->id);

            if (!in_array($p->status, ['in_progress', 'quality_check'], true)) {
                throw ValidationException::withMessages(['production' => 'Progress can be updated only while production is active or in quality check.']);
            }

            $planned = (float) $p->planned_quantity;
            $produced = max(0, (float) $data['produced_quantity']);
            $rejected = max(0, (float) ($data['rejected_quantity'] ?? 0));
            $waste = max(0, (float) ($data['waste_quantity'] ?? 0));

            if ($produced + $rejected > $planned + 0.0001) {
                throw ValidationException::withMessages(['produced_quantity' => 'Produced + rejected quantity cannot exceed planned quantity.']);
            }

            $old = $p->only(['produced_quantity', 'rejected_quantity', 'waste_quantity']);
            $p->update([
                'produced_quantity' => $produced,
                'rejected_quantity' => $rejected,
                'waste_quantity' => $waste,
            ]);

            // Keep item-level totals synchronized for single-item production jobs.
            $items = $p->items()->lockForUpdate()->get();
            if ($items->count() === 1) {
                $items->first()->update([
                    'produced_quantity' => $produced,
                    'rejected_quantity' => $rejected,
                ]);
            }

            $updated = $p->fresh(['items']);
            if ($userId) {
                $this->audit('progress_updated', $updated, $old, $updated->only(['produced_quantity', 'rejected_quantity', 'waste_quantity']), $userId, 'Production quantity updated');
            }

            return $updated;
        });
    }

    public function updateStep(ProductionStep $step, string $status, ?int $userId = null): ProductionStep
    {
        return DB::transaction(function () use ($step, $status, $userId) {
            $locked = ProductionStep::lockForUpdate()->findOrFail($step->id);

            if (!in_array($status, ['pending', 'in_progress', 'completed', 'skipped'], true)) {
                throw ValidationException::withMessages(['step' => 'Invalid production step status.']);
            }

            $old = $locked->status;
            $payload = ['status' => $status];

            if ($status === 'in_progress' && !$locked->started_at) {
                $payload['started_at'] = now();
            }
            if (in_array($status, ['completed', 'skipped'], true)) {
                $payload['completed_at'] = now();
            }
            if ($status === 'pending') {
                $payload['completed_at'] = null;
            }

            $locked->update($payload);
            $updated = $locked->fresh();

            if ($userId) {
                $this->audit('step_changed', $updated, ['status' => $old], ['status' => $status], $userId, 'Production step status changed');
            }

            return $updated;
        });
    }

    public function generateNumber(): string
    {
        $year = (int) now()->format('Y');
        $row = DB::table('production_sequences')->where('year', $year)->lockForUpdate()->first();

        if (!$row) {
            DB::table('production_sequences')->insert([
                'year' => $year,
                'last_number' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $number = 1;
        } else {
            $number = ((int) $row->last_number) + 1;
            DB::table('production_sequences')->where('id', $row->id)->update([
                'last_number' => $number,
                'updated_at' => now(),
            ]);
        }

        return 'PROD-'.$year.'-'.str_pad((string) $number, 6, '0', STR_PAD_LEFT);
    }

    private function assertCompletable(ProductionOrder $p): void
    {
        $planned = (float) $p->planned_quantity;
        $done = (float) $p->produced_quantity + (float) $p->rejected_quantity;

        if ($planned <= 0 || $done < $planned - 0.0001) {
            throw ValidationException::withMessages(['production' => 'Production cannot be completed until planned quantity is accounted for.']);
        }

        if ($p->quality_status === 'failed') {
            throw ValidationException::withMessages(['quality_status' => 'Quality check failed. Pass quality before completing production.']);
        }

        $unfinished = $p->steps->contains(fn (ProductionStep $step) => !in_array($step->status, ['completed', 'skipped'], true));
        if ($unfinished) {
            throw ValidationException::withMessages(['production' => 'Complete or skip all production steps before closing the job.']);
        }
    }

    private function audit(string $event, object $auditable, ?array $old, ?array $new, int $userId, string $description): void
    {
        if (!class_exists(\App\Models\AuditLog::class)) return;
        \App\Models\AuditLog::record($event, $auditable, $old, $new, $description, $userId);
    }

    private function defaultSteps(): array
    {
        return [
            ['artwork_check', 'Artwork Check'],
            ['printing', 'Label Printing'],
            ['bottle_prep', 'Bottle Preparation'],
            ['filling', 'Water Filling'],
            ['capping', 'Capping'],
            ['labeling', 'Label Application'],
            ['quality_check', 'Quality Check'],
            ['packing', 'Packing'],
        ];
    }
}
