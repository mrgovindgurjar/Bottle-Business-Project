<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuotationService
{
    public function __construct(private PricingService $pricingService) {}

    public function createQuotation(array $data, int $userId): Quotation
    {
        return DB::transaction(function () use ($data, $userId) {
            $quotation = Quotation::create([
                'quotation_number' => $this->generateQuotationNumber(),
                'customer_id' => $data['customer_id'],
                'design_id' => $data['design_id'] ?? null,
                'quotation_date' => $data['quotation_date'],
                'valid_until' => $data['valid_until'] ?? null,
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
                'terms_conditions' => $data['terms_conditions'] ?? null,
                'created_by' => $userId,
            ]);
            $this->syncItemsAndTotals($quotation, $data);
            return $quotation->fresh(['customer','items.product','design']);
        });
    }

    public function updateQuotation(Quotation $quotation, array $data): Quotation
    {
        if (!$quotation->isEditable()) throw ValidationException::withMessages(['quotation' => 'This quotation can no longer be edited.']);
        return DB::transaction(function () use ($quotation, $data) {
            $quotation->update([
                'customer_id' => $data['customer_id'], 'design_id' => $data['design_id'] ?? null,
                'quotation_date' => $data['quotation_date'], 'valid_until' => $data['valid_until'] ?? null,
                'notes' => $data['notes'] ?? null, 'terms_conditions' => $data['terms_conditions'] ?? null,
                'status' => 'draft',
            ]);
            $quotation->items()->delete();
            $this->syncItemsAndTotals($quotation, $data);
            return $quotation->fresh(['customer','items.product','design']);
        });
    }

    private function syncItemsAndTotals(Quotation $quotation, array $data): void
    {
        $subtotal = 0.0;
        $itemDiscountTotal = 0.0;
        $itemTaxTotal = 0.0;

        foreach (array_values($data['items']) as $index => $item) {
            $qty = (float) $item['quantity'];
            $price = (float) $item['unit_price'];
            $base = round($qty * $price, 2);
            $discountType = $item['discount_type'] ?? null;
            $discountValue = (float) ($item['discount_value'] ?? 0);
            $discount = $this->discount($base, $discountType, $discountValue);
            $net = max(0, round($base - $discount, 2));
            $taxRate = (float) ($item['tax_rate'] ?? 0);
            $tax = round($net * $taxRate / 100, 2);
            $lineTotal = round($net + $tax, 2);

            $quotation->items()->create([
                'product_id' => $item['product_id'] ?? null,
                'design_id' => $item['design_id'] ?? null,
                'description' => $item['description'], 'quantity' => $qty,
                'unit' => $item['unit'], 'unit_price' => $price,
                'discount_type' => $discountType, 'discount_value' => $discountValue,
                'discount_amount' => $discount, 'tax_rate' => $taxRate,
                'tax_amount' => $tax, 'line_total' => $lineTotal,
                'sort_order' => $index, 'metadata' => $item['metadata'] ?? null,
            ]);
            $subtotal += $base;
            $itemDiscountTotal += $discount;
            $itemTaxTotal += $tax;
        }

        $globalDiscount = $this->discount($subtotal - $itemDiscountTotal, $data['discount_type'] ?? null, (float) ($data['discount_value'] ?? 0));
        $taxable = max(0, round($subtotal - $itemDiscountTotal - $globalDiscount, 2));
        $taxRate = (float) ($data['tax_rate'] ?? 0);
        $globalTax = (($data['tax_type'] ?? 'none') === 'percentage') ? round($taxable * $taxRate / 100, 2) : 0;
        $shipping = round((float) ($data['shipping_amount'] ?? 0), 2);
        $other = round((float) ($data['other_amount'] ?? 0), 2);
        $grand = round($taxable + $itemTaxTotal + $globalTax + $shipping + $other, 2);

        $quotation->update([
            'subtotal' => round($subtotal, 2),
            'discount_type' => $data['discount_type'] ?? null,
            'discount_value' => (float) ($data['discount_value'] ?? 0),
            'discount_amount' => round($itemDiscountTotal + $globalDiscount, 2),
            'tax_type' => $data['tax_type'] ?? 'none', 'tax_rate' => $taxRate,
            'tax_amount' => round($itemTaxTotal + $globalTax, 2),
            'shipping_amount' => $shipping, 'other_amount' => $other,
            'grand_total' => $grand,
        ]);
    }

    private function discount(float $base, ?string $type, float $value): float
    {
        if ($value <= 0 || !$type) return 0.0;
        return round(min($base, $type === 'percentage' ? $base * min($value, 100) / 100 : $value), 2);
    }

    public function generateQuotationNumber(): string
    {
        $year = now()->format('Y');
        $sequence = DB::table('quotation_sequences')->where('year', $year)->lockForUpdate()->first();
        if (!$sequence) {
            DB::table('quotation_sequences')->insert(['year' => $year, 'next_number' => 2, 'created_at' => now(), 'updated_at' => now()]);
            $number = 1;
        } else {
            $number = (int) $sequence->next_number;
            DB::table('quotation_sequences')->where('year', $year)->update(['next_number' => $number + 1, 'updated_at' => now()]);
        }
        return 'QT-'.$year.'-'.str_pad((string) $number, 6, '0', STR_PAD_LEFT);
    }

    public function send(Quotation $quotation): Quotation
    {
        if (!in_array($quotation->status, ['draft','viewed'], true)) throw ValidationException::withMessages(['quotation' => 'Only draft/viewed quotations can be sent.']);
        $quotation->update(['status' => 'sent', 'sent_at' => now()]);
        return $quotation->fresh();
    }

    public function approve(Quotation $quotation, int $userId): Quotation
    {
        if (!in_array($quotation->status, ['sent','viewed'], true) || ($quotation->valid_until && $quotation->valid_until->isPast())) throw ValidationException::withMessages(['quotation' => 'This quotation cannot be approved.']);
        $quotation->update(['status' => 'approved', 'approved_by' => $userId, 'approved_at' => now()]);
        return $quotation->fresh();
    }

    public function reject(Quotation $quotation): Quotation
    {
        if (!in_array($quotation->status, ['sent','viewed'], true)) throw ValidationException::withMessages(['quotation' => 'This quotation cannot be rejected.']);
        $quotation->update(['status' => 'rejected']);
        return $quotation->fresh();
    }

    public function duplicate(Quotation $quotation, int $userId): Quotation
    {
        return DB::transaction(function () use ($quotation, $userId) {
            $copy = $quotation->replicate(['quotation_number','status','approved_by','approved_at','sent_at','converted_to_order_at']);
            $copy->quotation_number = $this->generateQuotationNumber();
            $copy->status = 'draft'; $copy->created_by = $userId;
            $copy->approved_by = null; $copy->approved_at = null; $copy->sent_at = null; $copy->converted_to_order_at = null;
            $copy->quotation_date = now()->toDateString();
            $copy->save();
            foreach ($quotation->items as $item) { $copy->items()->create($item->only(['product_id','design_id','description','quantity','unit','unit_price','discount_type','discount_value','discount_amount','tax_rate','tax_amount','line_total','sort_order','metadata'])); }
            return $copy->fresh(['customer','items.product','design']);
        });
    }

    public function convertToOrder(Quotation $quotation, int $userId): mixed
    {
        if (!$quotation->isConvertible()) throw ValidationException::withMessages(['quotation' => 'Only approved quotations can be converted, and only once.']);
        if (!class_exists(OrderService::class)) throw ValidationException::withMessages(['quotation' => 'OrderService is not available. Finish the existing Order module before conversion.']);
        $service = app(OrderService::class);
        if (!method_exists($service, 'createFromQuotation')) throw ValidationException::withMessages(['quotation' => 'Existing OrderService does not yet expose createFromQuotation().']);
        return DB::transaction(function () use ($quotation, $service, $userId) {
            $order = $service->createFromQuotation($quotation, $userId);
            $quotation->update(['status' => 'converted', 'converted_to_order_at' => now()]);
            return $order;
        });
    }

    public function expireOverdue(): int
    {
        return Quotation::whereIn('status', ['draft','sent','viewed'])->whereNotNull('valid_until')->whereDate('valid_until','<',today())->update(['status' => 'expired']);
    }
}
