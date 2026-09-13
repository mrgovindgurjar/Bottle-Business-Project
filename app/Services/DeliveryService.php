<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\BatchAllocation;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\InventoryItem;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryService
{
    public function create(array $data, int $userId): Delivery
    {
        return DB::transaction(function () use ($data, $userId) {
            $order = Order::with('items')->lockForUpdate()->findOrFail($data['order_id']);
            if (in_array($order->status, ['draft','cancelled','delivered'], true)) {
                throw ValidationException::withMessages(['order_id' => 'This order is not available for a new delivery.']);
            }

            $items = $this->validateItemsAgainstOrder($order, $data['items']);
            $delivery = Delivery::create([
                'delivery_number' => $this->generateNumber(),
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'delivery_date' => $data['delivery_date'],
                'scheduled_date' => $data['scheduled_date'] ?? null,
                'status' => 'ready',
                'delivery_address' => $data['delivery_address'] ?? $order->delivery_address,
                'delivery_city' => $data['delivery_city'] ?? $order->delivery_city,
                'delivery_state' => $data['delivery_state'] ?? $order->delivery_state,
                'delivery_pincode' => $data['delivery_pincode'] ?? $order->delivery_pincode,
                'assigned_to' => $data['assigned_to'] ?? null,
                'driver_name' => $data['driver_name'] ?? null,
                'driver_mobile' => $data['driver_mobile'] ?? null,
                'vehicle_number' => $data['vehicle_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'dispatch_notes' => $data['dispatch_notes'] ?? null,
                'created_by' => $userId,
            ]);

            foreach ($items as $index => $item) {
                $orderItem = $order->items->firstWhere('id', (int) $item['order_item_id']);
                $delivery->items()->create([
                    'order_item_id' => $orderItem->id,
                    'product_id' => $item['product_id'] ?? $orderItem->product_id,
                    'batch_id' => $item['batch_id'] ?? null,
                    'description' => $item['description'] ?? $orderItem->description,
                    'quantity' => $item['quantity'],
                    'delivered_quantity' => 0,
                    'unit' => $item['unit'] ?? $orderItem->unit,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $delivery->fresh(['order','customer.user','items.product','items.batch','assignee','creator']);
        });
    }

    public function update(Delivery $delivery, array $data): Delivery
    {
        if (!$delivery->canEdit()) throw ValidationException::withMessages(['delivery'=>'This delivery can no longer be edited.']);
        return DB::transaction(function () use ($delivery,$data) {
            $order = Order::with('items')->lockForUpdate()->findOrFail($delivery->order_id);
            $this->validateItemsAgainstOrder($order, $data['items'], $delivery->id);
            $delivery->update([
                'delivery_date'=>$data['delivery_date'], 'scheduled_date'=>$data['scheduled_date'] ?? null,
                'delivery_address'=>$data['delivery_address'] ?? $order->delivery_address,
                'delivery_city'=>$data['delivery_city'] ?? $order->delivery_city,
                'delivery_state'=>$data['delivery_state'] ?? $order->delivery_state,
                'delivery_pincode'=>$data['delivery_pincode'] ?? $order->delivery_pincode,
                'assigned_to'=>$data['assigned_to'] ?? null, 'driver_name'=>$data['driver_name'] ?? null,
                'driver_mobile'=>$data['driver_mobile'] ?? null, 'vehicle_number'=>$data['vehicle_number'] ?? null,
                'notes'=>$data['notes'] ?? null, 'dispatch_notes'=>$data['dispatch_notes'] ?? null,
            ]);
            $delivery->items()->delete();
            foreach (array_values($data['items']) as $item) {
                $orderItem=$order->items->firstWhere('id',(int)$item['order_item_id']);
                $delivery->items()->create([
                    'order_item_id'=>$orderItem->id,'product_id'=>$item['product_id'] ?? $orderItem->product_id,
                    'batch_id'=>$item['batch_id'] ?? null,'description'=>$item['description'] ?? $orderItem->description,
                    'quantity'=>(float)$item['quantity'],'delivered_quantity'=>0,'unit'=>$item['unit'] ?? $orderItem->unit,'notes'=>$item['notes'] ?? null,
                ]);
            }
            return $delivery->fresh(['order','customer.user','items.product','items.batch','assignee']);
        });
    }

    public function dispatch(Delivery $delivery, int $userId): Delivery
    {
        return DB::transaction(function () use ($delivery,$userId) {
            $delivery=Delivery::with(['items.product','items.batch','order'])->lockForUpdate()->findOrFail($delivery->id);
            if (!$delivery->canDispatch()) throw ValidationException::withMessages(['delivery'=>'This delivery cannot be dispatched in its current status.']);
            $batchService=app(BatchService::class); $inventory=app(InventoryService::class);

            foreach ($delivery->items as $item) {
                if (!$item->batch_id) throw ValidationException::withMessages(['items'=>'Select a batch for every delivery item before dispatch.']);
                $batch=Batch::lockForUpdate()->findOrFail($item->batch_id);
                if ((int)$batch->product_id !== (int)$item->product_id) throw ValidationException::withMessages(['items'=>'Selected batch does not belong to the selected product.']);
                $qty=(float)$item->quantity;
                $batchService->allocate($batch,[
                    'customer_id'=>$delivery->customer_id,'order_id'=>$delivery->order_id,'delivery_id'=>$delivery->id,
                    'quantity'=>$qty,'notes'=>'Dispatch allocation for '.$delivery->delivery_number,
                ],$userId);

                $inventoryItem=InventoryItem::where('product_id',$item->product_id)->where('status','active')->first();
                if (!$inventoryItem && $item->product) $inventoryItem=InventoryItem::where('sku',$item->product->sku)->where('status','active')->first();
                if (!$inventoryItem) throw ValidationException::withMessages(['items'=>'No active inventory item is linked to '.$item->product?->name.'.']);
                $movement=$inventory->issue($inventoryItem,$qty,$userId,$batch,'delivery',$delivery->id,'Dispatched in '.$delivery->delivery_number);
                $item->update(['inventory_movement_id'=>$movement->id]);
            }

            $delivery->update(['status'=>'out_for_delivery','dispatched_at'=>now(),'dispatched_by'=>$userId]);
            $delivery->order()->whereIn('status',['confirmed','in_production','ready'])->update(['status'=>'dispatched']);
            return $delivery->fresh(['order','customer','items.product','items.batch','assignee','dispatcher']);
        });
    }

    public function deliver(Delivery $delivery, array $data, int $userId): Delivery
    {
        return DB::transaction(function () use ($delivery,$data,$userId) {
            $delivery=Delivery::with(['items','order'])->lockForUpdate()->findOrFail($delivery->id);
            if (!$delivery->canDeliver()) throw ValidationException::withMessages(['delivery'=>'Only an out-for-delivery delivery can be completed.']);
            foreach ($delivery->items as $item) {
                $qty=(float)($data['delivered_quantities'][$item->id] ?? $item->quantity);
                if ($qty < 0 || $qty > (float)$item->quantity + 0.0001) throw ValidationException::withMessages(["delivered_quantities.{$item->id}"=>'Delivered quantity is invalid.']);
                $item->update(['delivered_quantity'=>$qty]);
            }
            $delivery->update([
                'status'=>'delivered','receiver_name'=>$data['receiver_name'],'receiver_mobile'=>$data['receiver_mobile'] ?? null,
                'delivered_at'=>now(),'delivered_by'=>$userId,
            ]);
            $this->syncOrderDeliveryStatus($delivery->order_id);
            return $delivery->fresh(['order','customer','items.product','items.batch','deliverer']);
        });
    }

    public function fail(Delivery $delivery, string $reason, int $userId): Delivery
    {
        return DB::transaction(function () use ($delivery,$reason,$userId) {
            $delivery=Delivery::with(['items','order'])->lockForUpdate()->findOrFail($delivery->id);
            if (!in_array($delivery->status,['out_for_delivery','ready'],true)) throw ValidationException::withMessages(['delivery'=>'This delivery cannot be marked failed.']);
            if ($delivery->status==='out_for_delivery') {
                $inventory=app(InventoryService::class);
                foreach ($delivery->items as $item) {
                    if ($item->inventory_movement_id) {
                        $movement=$item->inventoryMovement()->first();
                        if ($movement) {
                            $inventoryItem=$movement->item()->first();
                            if ($inventoryItem) $inventory->receive($inventoryItem,(float)$movement->quantity,$userId,(float)$movement->unit_cost,$movement->batch,'delivery_return',$delivery->id,'Return from failed delivery '.$delivery->delivery_number);
                        }
                    }
                    $allocation=BatchAllocation::where('delivery_id',$delivery->id)->where('batch_id',$item->batch_id)->whereNull('reversed_at')->latest('id')->first();
                    if ($allocation) app(BatchService::class)->reverseAllocation($allocation,$userId,'Failed delivery '.$delivery->delivery_number);
                }
            }
            $delivery->update(['status'=>'failed','failed_at'=>now(),'failed_reason'=>$reason]);
            return $delivery->fresh(['order','customer','items.product','items.batch']);
        });
    }

    public function cancel(Delivery $delivery, int $userId): Delivery
    {
        return DB::transaction(function () use ($delivery,$userId) {
            if (!$delivery->canCancel()) throw ValidationException::withMessages(['delivery'=>'This delivery cannot be cancelled.']);
            if ($delivery->status==='out_for_delivery') $this->fail($delivery,'Cancelled before completion',$userId);
            $delivery->update(['status'=>'cancelled','cancelled_at'=>now(),'cancelled_by'=>$userId]);
            return $delivery->fresh();
        });
    }

    private function validateItemsAgainstOrder(Order $order, array $items, ?int $ignoreDeliveryId=null): array
    {
        if (!$items) throw ValidationException::withMessages(['items'=>'Add at least one delivery item.']);
        $already=DeliveryItem::query()->whereHas('delivery',fn($q)=>$q->where('order_id',$order->id)->whereNotIn('status',['cancelled','failed']))
            ->when($ignoreDeliveryId,fn($q)=>$q->where('delivery_id','!=',$ignoreDeliveryId))->select('order_item_id',DB::raw('SUM(quantity) as total'))->groupBy('order_item_id')->pluck('total','order_item_id');
        $requested=[];
        foreach ($items as $item) {
            $orderItem=$order->items->firstWhere('id',(int)$item['order_item_id']);
            if (!$orderItem) throw ValidationException::withMessages(['items'=>'Every delivery item must belong to the selected order.']);
            $requested[$orderItem->id]=($requested[$orderItem->id]??0)+(float)$item['quantity'];
        }
        foreach ($requested as $orderItemId => $qty) {
            $orderItem=$order->items->firstWhere('id',(int)$orderItemId);
            $remaining=(float)$orderItem->quantity-(float)($already[$orderItem->id]??0);
            if ($qty<=0 || $qty>$remaining+0.0001) throw ValidationException::withMessages(['items'=>'Delivery quantity exceeds the remaining order quantity for '.$orderItem->description.'.']);
        }
        return $items;
    }

    private function syncOrderDeliveryStatus(int $orderId): void
    {
        $order=Order::with('items')->lockForUpdate()->findOrFail($orderId);
        $delivered=DeliveryItem::whereHas('delivery',fn($q)=>$q->where('order_id',$orderId)->where('status','delivered'))->select('order_item_id',DB::raw('SUM(delivered_quantity) as total'))->groupBy('order_item_id')->pluck('total','order_item_id');
        $complete=$order->items->every(fn($i)=>(float)($delivered[$i->id]??0)>=(float)$i->quantity-0.0001);
        $order->update(['status'=>$complete?'delivered':'dispatched']);
    }

    public function generateNumber(): string
    {
        return DB::transaction(function () {
            $year=(int)now()->year;
            $row=DB::table('delivery_sequences')->where('year',$year)->lockForUpdate()->first();
            if (!$row) { DB::table('delivery_sequences')->insert(['year'=>$year,'last_number'=>1,'created_at'=>now(),'updated_at'=>now()]); $n=1; }
            else { $n=(int)$row->last_number+1; DB::table('delivery_sequences')->where('id',$row->id)->update(['last_number'=>$n,'updated_at'=>now()]); }
            return 'DLV-'.$year.'-'.str_pad((string)$n,6,'0',STR_PAD_LEFT);
        });
    }
}
