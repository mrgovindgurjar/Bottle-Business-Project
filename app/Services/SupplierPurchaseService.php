<?php

namespace App\Services;

use App\Models\DocumentSequence;
use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupplierPurchaseService
{
    public function createSupplier(array $data): Supplier
    {
        return DB::transaction(fn() => Supplier::create($data));
    }

    public function updateSupplier(Supplier $supplier, array $data): Supplier
    {
        $supplier->update($data);
        return $supplier->fresh();
    }

    public function generateSupplierCode(): string
    {
        return DB::transaction(function () {
            $seq = DocumentSequence::where('document_type','supplier')->where('year',now()->year)->lockForUpdate()->first();
            if (!$seq) $seq = DocumentSequence::create(['document_type'=>'supplier','year'=>now()->year,'next_number'=>2]);
            else { $n=$seq->next_number; $seq->increment('next_number'); return 'SUP-'.now()->year.'-'.str_pad($n,6,'0',STR_PAD_LEFT); }
            return 'SUP-'.now()->year.'-000001';
        });
    }

    public function generatePurchaseNumber(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $seq = DocumentSequence::where('document_type','purchase')->where('year',$year)->lockForUpdate()->first();
            if (!$seq) {
                DocumentSequence::create(['document_type'=>'purchase','year'=>$year,'next_number'=>2]);
                return "PUR-{$year}-000001";
            }
            $n = $seq->next_number;
            $seq->increment('next_number');
            return "PUR-{$year}-".str_pad($n,6,'0',STR_PAD_LEFT);
        });
    }

    public function createPurchase(array $data, int $userId): Purchase
    {
        return DB::transaction(function () use ($data,$userId) {
            $items = $data['items'] ?? [];
            if (count($items) < 1) throw ValidationException::withMessages(['items'=>'Add at least one purchase item.']);
            $purchase = Purchase::create([
                'purchase_number'=>$this->generatePurchaseNumber(),
                'supplier_id'=>$data['supplier_id'], 'purchase_date'=>$data['purchase_date'], 'expected_date'=>$data['expected_date'] ?? null,
                'status'=>$data['status'] ?? 'draft', 'discount_type'=>$data['discount_type'] ?? null,
                'discount_value'=>(float)($data['discount_value'] ?? 0), 'tax_rate'=>(float)($data['tax_rate'] ?? 0),
                'shipping_amount'=>(float)($data['shipping_amount'] ?? 0), 'other_amount'=>(float)($data['other_amount'] ?? 0),
                'notes'=>$data['notes'] ?? null, 'terms_conditions'=>$data['terms_conditions'] ?? null, 'created_by'=>$userId,
            ]);
            $this->replaceItems($purchase,$items);
            $this->recalculate($purchase);
            return $purchase->fresh(['supplier','items.product','items.inventoryItem']);
        });
    }

    public function updatePurchase(Purchase $purchase, array $data): Purchase
    {
        return DB::transaction(function () use ($purchase,$data) {
            if (in_array($purchase->status,['received','cancelled'],true)) {
                throw ValidationException::withMessages(['status'=>'This purchase can no longer be edited.']);
            }
            $purchase->update([
                'supplier_id'=>$data['supplier_id'], 'purchase_date'=>$data['purchase_date'], 'expected_date'=>$data['expected_date'] ?? null,
                'discount_type'=>$data['discount_type'] ?? null, 'discount_value'=>(float)($data['discount_value'] ?? 0),
                'tax_rate'=>(float)($data['tax_rate'] ?? 0), 'shipping_amount'=>(float)($data['shipping_amount'] ?? 0),
                'other_amount'=>(float)($data['other_amount'] ?? 0), 'notes'=>$data['notes'] ?? null,
                'terms_conditions'=>$data['terms_conditions'] ?? null,
            ]);
            $this->replaceItems($purchase,$data['items'] ?? []);
            $this->recalculate($purchase);
            return $purchase->fresh(['supplier','items.product','items.inventoryItem']);
        });
    }

    private function replaceItems(Purchase $purchase, array $items): void
    {
        $existingReceived = $purchase->items()->sum('received_quantity');
        if ($existingReceived > 0) throw ValidationException::withMessages(['items'=>'Received purchases cannot have their items replaced.']);
        $purchase->items()->delete();
        foreach (array_values($items) as $index=>$item) {
            $qty=(float)($item['quantity']??0); $price=(float)($item['unit_price']??0);
            if($qty<=0) continue;
            $base=$qty*$price;
            $discount=$this->discount($base,$item['discount_type']??null,(float)($item['discount_value']??0));
            $taxable=max(0,$base-$discount); $tax=round($taxable*((float)($item['tax_rate']??0))/100,2);
            PurchaseItem::create([
                'purchase_id'=>$purchase->id,'product_id'=>$item['product_id']??null,'inventory_item_id'=>$item['inventory_item_id']??null,
                'description'=>$item['description'] ?? 'Purchase item','quantity'=>$qty,'unit'=>$item['unit']??'unit','unit_price'=>$price,
                'discount_type'=>$item['discount_type']??null,'discount_value'=>(float)($item['discount_value']??0),'discount_amount'=>$discount,
                'tax_rate'=>(float)($item['tax_rate']??0),'tax_amount'=>$tax,'line_total'=>round($taxable+$tax,2),'sort_order'=>$index,
                'metadata'=>$item['metadata']??null,
            ]);
        }
    }

    public function recalculate(Purchase $purchase): Purchase
    {
        $purchase->loadMissing('items');
        $subtotal=round((float)$purchase->items->sum(fn($i)=>(float)$i->quantity*(float)$i->unit_price),2);
        $discount=$this->discount($subtotal,$purchase->discount_type,(float)$purchase->discount_value);
        $taxable=max(0,$subtotal-$discount); $tax=round($purchase->items->sum('tax_amount') + ($taxable*((float)$purchase->tax_rate)/100),2);
        $grand=round($taxable+$tax+(float)$purchase->shipping_amount+(float)$purchase->other_amount,2);
        $paid=(float)$purchase->payments()->sum('amount'); $balance=max(0,round($grand-$paid,2));
        $purchase->update([
            'subtotal'=>$subtotal,'discount_amount'=>$discount,'tax_amount'=>$tax,'grand_total'=>$grand,
            'paid_amount'=>$paid,'balance_amount'=>$balance,'payment_status'=>$balance<=0?'paid':($paid>0?'partial':'unpaid'),
        ]);
        return $purchase->fresh();
    }

    private function discount(float $base, ?string $type, float $value): float
    {
        if($value<=0) return 0;
        return round(min($base,$type==='percent' ? $base*$value/100 : $value),2);
    }

    public function receive(Purchase $purchase, array $quantities, int $userId): Purchase
    {
        return DB::transaction(function () use ($purchase,$quantities,$userId) {
            if ($purchase->status==='cancelled') throw ValidationException::withMessages(['purchase'=>'Cancelled purchase cannot be received.']);
            $inventory = app(InventoryService::class);
            $purchase->load('items.inventoryItem','items.product');
            foreach ($purchase->items as $item) {
                $qty=(float)($quantities[$item->id]??0); $remaining=max(0,(float)$item->quantity-(float)$item->received_quantity);
                if($qty<=0) continue;
                if($qty>$remaining+0.0001) throw ValidationException::withMessages(["items.{$item->id}"=>'Received quantity cannot exceed remaining quantity.']);
                $inv=$item->inventoryItem;
                if(!$inv && $item->product_id){
                    $inv=InventoryItem::where('product_id',$item->product_id)->first();
                }
                if(!$inv) throw ValidationException::withMessages(["items.{$item->id}"=>'Link this item to an Inventory Item before receiving stock.']);
                $inventory->receive($inv,$qty,$userId,(float)$item->unit_price,null,'purchase',$purchase->id,'Purchase '.$purchase->purchase_number);
                $item->increment('received_quantity',$qty);
            }
            $purchase->refresh()->load('items');
            $complete=$purchase->items->every(fn($i)=>(float)$i->received_quantity >= (float)$i->quantity-0.0001);
            $any=$purchase->items->contains(fn($i)=>(float)$i->received_quantity>0);
            $purchase->update(['status'=>$complete?'received':($any?'partially_received':'ordered'),'received_at'=>$complete?now():null]);
            return $purchase->fresh(['supplier','items.product','items.inventoryItem']);
        });
    }

    public function recordPayment(Purchase $purchase, array $data, int $userId): SupplierPayment
    {
        return DB::transaction(function () use ($purchase,$data,$userId) {
            $purchase->refresh(); $amount=round((float)$data['amount'],2);
            if($amount<=0 || $amount>(float)$purchase->balance_amount+0.01) throw ValidationException::withMessages(['amount'=>'Payment must be greater than zero and cannot exceed the outstanding balance.']);
            $payment=SupplierPayment::create([
                'supplier_id'=>$purchase->supplier_id,'purchase_id'=>$purchase->id,'payment_date'=>$data['payment_date'],
                'amount'=>$amount,'method'=>$data['method'],'reference'=>$data['reference']??null,'notes'=>$data['notes']??null,'created_by'=>$userId,
            ]);
            $this->recalculate($purchase);
            return $payment;
        });
    }
}
