<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function create(array $data, int $userId): Order
    {
        return DB::transaction(function () use ($data, $userId) {
            if (!empty($data['quotation_id'])) {
                $quotation = Quotation::find($data['quotation_id']);
                if (!$quotation || $quotation->status !== 'approved') {
                    throw ValidationException::withMessages(['quotation_id' => 'Only an approved quotation can be linked to a new order.']);
                }
                if ((int) $quotation->customer_id !== (int) $data['customer_id']) {
                    throw ValidationException::withMessages(['customer_id' => 'The selected quotation belongs to a different customer.']);
                }
            }

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'quotation_id' => $data['quotation_id'] ?? null,
                'customer_id' => $data['customer_id'],
                'design_id' => $data['design_id'] ?? null,
                'order_date' => $data['order_date'],
                'required_date' => $data['required_date'] ?? null,
                'status' => 'draft',
                'delivery_address' => $data['delivery_address'] ?? null,
                'delivery_city' => $data['delivery_city'] ?? null,
                'delivery_state' => $data['delivery_state'] ?? null,
                'delivery_pincode' => $data['delivery_pincode'] ?? null,
                'notes' => $data['notes'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'created_by' => $userId,
            ]);

            $this->syncItemsAndTotals($order, $data['items'], $data);

            if (!empty($data['quotation_id'])) {
                $quotation = Quotation::find($data['quotation_id']);
                if ($quotation && $quotation->status === 'approved' && !$quotation->converted_to_order_at) {
                    $quotation->update(['status' => 'converted', 'converted_to_order_at' => now()]);
                }
            }

            event(new \App\Events\OrderCreated());

            return $order->fresh(['customer.user', 'items.product', 'items.design', 'quotation', 'design']);
        });
    }

    public function update(Order $order, array $data): Order
    {
        if (!$order->isEditable()) {
            throw ValidationException::withMessages(['order' => 'Only draft or on-hold orders can be edited.']);
        }

        return DB::transaction(function () use ($order, $data) {
            if (!empty($data['quotation_id'])) {
                $quotation = Quotation::find($data['quotation_id']);
                if (!$quotation || (int) $quotation->customer_id !== (int) $data['customer_id']) {
                    throw ValidationException::withMessages(['quotation_id' => 'The selected quotation does not belong to this customer.']);
                }
            }

            $order->update([
                'customer_id' => $data['customer_id'],
                'quotation_id' => $data['quotation_id'] ?? null,
                'design_id' => $data['design_id'] ?? null,
                'order_date' => $data['order_date'],
                'required_date' => $data['required_date'] ?? null,
                'delivery_address' => $data['delivery_address'] ?? null,
                'delivery_city' => $data['delivery_city'] ?? null,
                'delivery_state' => $data['delivery_state'] ?? null,
                'delivery_pincode' => $data['delivery_pincode'] ?? null,
                'notes' => $data['notes'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
            ]);

            $order->items()->delete();
            $this->syncItemsAndTotals($order, $data['items'], $data);

            return $order->fresh(['customer.user', 'items.product', 'items.design', 'quotation', 'design']);
        });
    }

    public function createFromQuotation(Quotation $quotation, int $userId): Order
    {
        return DB::transaction(function () use ($quotation, $userId) {
            $quotation = Quotation::query()->lockForUpdate()->findOrFail($quotation->id);

            if ($quotation->status !== 'approved' || $quotation->converted_to_order_at) {
                $existing = Order::where('quotation_id', $quotation->id)->latest('id')->first();
                if ($existing) return $existing;
                throw ValidationException::withMessages(['quotation' => 'Only an approved quotation can be converted, and it can be converted only once.']);
            }

            $existing = Order::where('quotation_id', $quotation->id)->latest('id')->lockForUpdate()->first();
            if ($existing) {
                $quotation->update(['status' => 'converted', 'converted_to_order_at' => $quotation->converted_to_order_at ?: now()]);
                return $existing;
            }

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'quotation_id' => $quotation->id,
                'customer_id' => $quotation->customer_id,
                'design_id' => $quotation->design_id,
                'order_date' => today(),
                'required_date' => null,
                'status' => 'confirmed',
                'subtotal' => 0,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'shipping_amount' => 0,
                'other_amount' => 0,
                'grand_total' => 0,
                'notes' => $quotation->notes,
                'internal_notes' => 'Created from quotation '.$quotation->quotation_number.'.',
                'created_by' => $userId,
                'confirmed_by' => $userId,
                'confirmed_at' => now(),
            ]);

            foreach ($quotation->items as $index => $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'design_id' => $item->design_id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'discount_amount' => $item->discount_amount,
                    'tax_rate' => $item->tax_rate,
                    'tax_amount' => $item->tax_amount,
                    'line_total' => $item->line_total,
                    'sort_order' => $index,
                    'metadata' => $item->metadata,
                ]);
            }

            $order->update([
                'subtotal' => $quotation->subtotal,
                'discount_amount' => $quotation->discount_amount,
                'tax_amount' => $quotation->tax_amount,
                'shipping_amount' => $quotation->shipping_amount,
                'other_amount' => $quotation->other_amount,
                'grand_total' => $quotation->grand_total,
            ]);

            $quotation->update(['status' => 'converted', 'converted_to_order_at' => now()]);
            event(new \App\Events\OrderCreated());
            event(new \App\Events\OrderApproved());

            return $order->fresh(['customer.user', 'items.product', 'items.design', 'quotation', 'design']);
        });
    }

    public function changeStatus(Order $order, string $status, int $userId): Order
    {
        $allowed = [
            'draft' => ['confirmed', 'cancelled'],
            'confirmed' => ['in_production', 'on_hold', 'cancelled'],
            'in_production' => ['ready', 'on_hold', 'cancelled'],
            'ready' => ['dispatched', 'on_hold', 'cancelled'],
            'dispatched' => ['delivered', 'on_hold'],
            'on_hold' => ['confirmed', 'cancelled'],
            'delivered' => [],
            'cancelled' => [],
        ];

        if (!in_array($status, $allowed[$order->status] ?? [], true)) {
            throw ValidationException::withMessages(['order' => "Cannot move order from {$order->status} to {$status}."]);
        }

        $payload = ['status' => $status];
        if ($status === 'confirmed') {
            $payload['confirmed_by'] = $userId;
            $payload['confirmed_at'] = now();
        }
        if ($status === 'cancelled') {
            $payload['cancelled_at'] = now();
        }

        $order->update($payload);
        if ($status === 'confirmed') {
            event(new \App\Events\OrderApproved());
        }
        return $order->fresh();
    }

    public function duplicate(Order $order, int $userId): Order
    {
        return DB::transaction(function () use ($order, $userId) {
            $copy = $order->replicate(['order_number', 'status', 'confirmed_by', 'confirmed_at', 'cancelled_at']);
            $copy->order_number = $this->generateOrderNumber();
            $copy->status = 'draft';
            $copy->created_by = $userId;
            $copy->quotation_id = null;
            $copy->order_date = today();
            $copy->confirmed_by = null;
            $copy->confirmed_at = null;
            $copy->cancelled_at = null;
            $copy->save();

            foreach ($order->items as $item) {
                $copy->items()->create($item->only([
                    'product_id', 'design_id', 'description', 'quantity', 'unit', 'unit_price',
                    'discount_amount', 'tax_rate', 'tax_amount', 'line_total', 'sort_order', 'metadata',
                ]));
            }

            return $copy->fresh(['customer.user', 'items.product', 'items.design', 'design']);
        });
    }

    public function generateOrderNumber(): string
    {
        $year = (int) now()->format('Y');
        $sequence = DB::table('order_sequences')->where('year', $year)->lockForUpdate()->first();

        if (!$sequence) {
            DB::table('order_sequences')->insert([
                'year' => $year,
                'next_number' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $number = 1;
        } else {
            $number = (int) $sequence->next_number;
            DB::table('order_sequences')->where('year', $year)->update([
                'next_number' => $number + 1,
                'updated_at' => now(),
            ]);
        }

        return 'ORD-'.$year.'-'.str_pad((string) $number, 6, '0', STR_PAD_LEFT);
    }

    private function syncItemsAndTotals(Order $order, array $items, array $data = []): void
    {
        $subtotal = 0.0;
        $discount = 0.0;
        $tax = 0.0;

        foreach (array_values($items) as $index => $item) {
            $qty = max(0, (float) $item['quantity']);
            $price = max(0, (float) $item['unit_price']);
            $base = round($qty * $price, 2);
            $itemDiscount = min($base, max(0, (float) ($item['discount_amount'] ?? 0)));
            $taxable = max(0, round($base - $itemDiscount, 2));
            $taxRate = min(100, max(0, (float) ($item['tax_rate'] ?? 0)));
            $itemTax = round($taxable * $taxRate / 100, 2);
            $lineTotal = round($taxable + $itemTax, 2);

            $order->items()->create([
                'product_id' => $item['product_id'] ?? null,
                'design_id' => $item['design_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $qty,
                'unit' => $item['unit'] ?? 'bottle',
                'unit_price' => $price,
                'discount_amount' => $itemDiscount,
                'tax_rate' => $taxRate,
                'tax_amount' => $itemTax,
                'line_total' => $lineTotal,
                'sort_order' => $index,
                'metadata' => $item['metadata'] ?? null,
            ]);

            $subtotal += $base;
            $discount += $itemDiscount;
            $tax += $itemTax;
        }

        $shipping = round(max(0, (float) ($data['shipping_amount'] ?? 0)), 2);
        $other = round(max(0, (float) ($data['other_amount'] ?? 0)), 2);

        $order->update([
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discount, 2),
            'tax_amount' => round($tax, 2),
            'shipping_amount' => $shipping,
            'other_amount' => $other,
            'grand_total' => round(max(0, $subtotal - $discount + $tax + $shipping + $other), 2),
        ]);
    }
}
