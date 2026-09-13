<?php
namespace App\Services;

use App\Models\Batch;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    
    public function createItem(array $data, int $userId): InventoryItem
    {
        return DB::transaction(function () use ($data,$userId) {
            $opening = (float)($data['opening_quantity'] ?? 0);
            $item = InventoryItem::create([
                'product_id'=>$data['product_id'] ?? null,
                'name'=>$data['name'],'sku'=>$data['sku'],'category'=>$data['category'],
                'unit'=>$data['unit'],'location'=>$data['location'] ?? null,
                'on_hand'=>$opening,'reserved'=>0,
                'reorder_level'=>(float)($data['reorder_level'] ?? 0),
                'reorder_quantity'=>(float)($data['reorder_quantity'] ?? 0),
                'average_cost'=>(float)($data['average_cost'] ?? 0),
                'status'=>$data['status'],'notes'=>$data['notes'] ?? null,
            ]);
            if ($opening > 0) $this->movement($item,'opening',$opening,(float)$item->average_cost,$userId,null,null,'Opening stock');
            return $item->fresh('product');
        });
    }

    public function updateItem(InventoryItem $item, array $data): InventoryItem
    {
        $item->update($data);
        return $item->fresh('product');
    }

    public function receive(InventoryItem $item, float $quantity, int $userId, ?float $unitCost=null, ?Batch $batch=null, ?string $referenceType=null, ?int $referenceId=null, ?string $notes=null): InventoryMovement
    {
        return DB::transaction(function () use ($item,$quantity,$userId,$unitCost,$batch,$referenceType,$referenceId,$notes) {
            if ($quantity <= 0) throw ValidationException::withMessages(['quantity'=>'Quantity must be greater than zero.']);
            $locked = InventoryItem::lockForUpdate()->findOrFail($item->id);
            $oldQty=(float)$locked->on_hand; $oldCost=(float)$locked->average_cost;
            $newQty=$oldQty+$quantity;
            $cost=$unitCost ?? $oldCost;
            $newCost=$newQty>0 ? (($oldQty*$oldCost)+($quantity*$cost))/$newQty : $cost;
            $locked->update(['on_hand'=>$newQty,'average_cost'=>round($newCost,2)]);
            return $this->movement($locked,'receipt',$quantity,$cost,$userId,$batch,$referenceType,$referenceId,$notes);
        });
    }

    public function issue(InventoryItem $item, float $quantity, int $userId, ?Batch $batch=null, ?string $referenceType=null, ?int $referenceId=null, ?string $notes=null): InventoryMovement
    {
        return DB::transaction(function () use ($item,$quantity,$userId,$batch,$referenceType,$referenceId,$notes) {
            if ($quantity <= 0) throw ValidationException::withMessages(['quantity'=>'Quantity must be greater than zero.']);
            $locked=InventoryItem::lockForUpdate()->findOrFail($item->id);
            $available=(float)$locked->on_hand-(float)$locked->reserved;
            if ($quantity > $available+0.0001) throw ValidationException::withMessages(['quantity'=>'Insufficient available stock.']);
            $locked->decrement('on_hand',$quantity);
            return $this->movement($locked,'issue',$quantity,(float)$locked->average_cost,$userId,$batch,$referenceType,$referenceId,$notes);
        });
    }

    public function adjust(InventoryItem $item, float $delta, int $userId, ?string $notes=null): InventoryMovement
    {
        return DB::transaction(function () use ($item,$delta,$userId,$notes) {
            if (abs($delta)<0.000001) throw ValidationException::withMessages(['quantity'=>'Adjustment cannot be zero.']);
            $locked=InventoryItem::lockForUpdate()->findOrFail($item->id);
            if ($delta<0 && abs($delta)>(float)$locked->on_hand-(float)$locked->reserved+0.0001) throw ValidationException::withMessages(['quantity'=>'Adjustment would make available stock negative.']);
            $locked->increment('on_hand',$delta);
            return $this->movement($locked,$delta>0?'adjustment_in':'adjustment_out',abs($delta),(float)$locked->average_cost,$userId,null,'adjustment',null,$notes);
        });
    }

    public function reserve(InventoryItem $item, float $quantity, int $userId, ?string $referenceType=null, ?int $referenceId=null, ?string $notes=null): InventoryMovement
    {
        return DB::transaction(function () use ($item,$quantity,$userId,$referenceType,$referenceId,$notes) {
            if ($quantity<=0) throw ValidationException::withMessages(['quantity'=>'Quantity must be greater than zero.']);
            $locked=InventoryItem::lockForUpdate()->findOrFail($item->id);
            $available=(float)$locked->on_hand-(float)$locked->reserved;
            if($quantity>$available+0.0001) throw ValidationException::withMessages(['quantity'=>'Insufficient available stock to reserve.']);
            $locked->increment('reserved',$quantity);
            return $this->movement($locked,'reserve',$quantity,(float)$locked->average_cost,$userId,null,$referenceType,$referenceId,$notes);
        });
    }

    public function releaseReservation(InventoryItem $item, float $quantity, int $userId, ?string $referenceType=null, ?int $referenceId=null, ?string $notes=null): InventoryMovement
    {
        return DB::transaction(function () use ($item,$quantity,$userId,$referenceType,$referenceId,$notes) {
            if($quantity<=0) throw ValidationException::withMessages(['quantity'=>'Quantity must be greater than zero.']);
            $locked=InventoryItem::lockForUpdate()->findOrFail($item->id);
            if($quantity>(float)$locked->reserved+0.0001) throw ValidationException::withMessages(['quantity'=>'Cannot release more than reserved stock.']);
            $locked->decrement('reserved',$quantity);
            return $this->movement($locked,'release_reservation',$quantity,(float)$locked->average_cost,$userId,null,$referenceType,$referenceId,$notes);
        });
    }

    public function syncBatch(Batch $batch, int $userId): ?InventoryMovement
    {
        return DB::transaction(function () use ($batch,$userId) {
            $batch=$batch->fresh(['product']);
            if(!$batch || !$batch->product_id || (float)$batch->available_quantity<=0) return null;
            if(!in_array($batch->status,['released','allocated','exhausted'],true) || $batch->quality_status!=='passed') return null;
            $exists=InventoryMovement::where('batch_id',$batch->id)->where('movement_type','production_in')->exists();
            if($exists) return null;
            $product=$batch->product;
            $item=InventoryItem::firstOrCreate(
                ['sku'=>$product->sku],
                ['product_id'=>$product->id,'name'=>$product->name,'category'=>'finished_goods','unit'=>$product->unit ?? 'bottle','location'=>'Finished Goods','on_hand'=>0,'reserved'=>0,'reorder_level'=>0,'reorder_quantity'=>0,'average_cost'=>0,'status'=>'active']
            );
            if(!$item->product_id) $item->update(['product_id'=>$product->id]);
            return $this->receive($item,(float)$batch->available_quantity,$userId,null,$batch,'batch',$batch->id,'Finished goods received from '.$batch->batch_number);
        });
    }

    public function movement(InventoryItem $item,string $type,float $qty,float $cost,int $userId,?Batch $batch=null,?string $referenceType=null,?int $referenceId=null,?string $notes=null): InventoryMovement
    {
        return InventoryMovement::create([
            'inventory_item_id'=>$item->id,'batch_id'=>$batch?->id,'movement_type'=>$type,
            'quantity'=>$qty,'unit_cost'=>$cost,'reference_type'=>$referenceType,'reference_id'=>$referenceId,
            'movement_date'=>now(),'performed_by'=>$userId,'notes'=>$notes,
        ]);
    }

    public function lowStockQuery()
    {
        return InventoryItem::where('status','active')->whereColumn('on_hand','<=','reorder_level')->where('reorder_level','>',0);
    }
}
