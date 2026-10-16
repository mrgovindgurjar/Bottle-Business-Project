<?php

namespace App\Services;

use App\Models\{Customer, Invoice, InvoiceItem, Order};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function generateInvoiceNumber(): string
    {
        return DB::transaction(function () {
            $key = 'invoice:' . now()->year;
            $row = DB::table('finance_sequences')->where('sequence_key', $key)->lockForUpdate()->first();

            if (!$row) {
                DB::table('finance_sequences')->insert([
                    'sequence_key' => $key,
                    'current_number' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $number = 1;
            } else {
                $number = (int) $row->current_number + 1;
                DB::table('finance_sequences')->where('id', $row->id)->update([
                    'current_number' => $number,
                    'updated_at' => now(),
                ]);
            }

            return sprintf('INV-%d-%06d', now()->year, $number);
        });
    }

    public function snapshotCustomer(Customer $customer): array
    {
        return [
            'billing_name' => $customer->business_name,
            'billing_contact_name' => $customer->contact_name ?? null,
            'billing_address' => $customer->address ?? null,
            'billing_city' => $customer->city ?? null,
            'billing_state' => $customer->state ?? null,
            'billing_pincode' => $customer->pincode ?? null,
            'billing_gstin' => $customer->gstin ?? null,
            'billing_mobile' => $customer->mobile ?? null,
            'billing_email' => $customer->email ?? null,
        ];
    }

    public function calculateTotals(array $items, array $data): array
    {
        $subtotal = 0.0;
        $discount = 0.0;
        $tax = 0.0;
        $normalized = [];

        foreach (array_values($items) as $idx => $row) {
            $qty = (float) $row['quantity'];
            $price = (float) $row['unit_price'];
            $line = round($qty * $price, 2);
            $requestedDiscount = max(0, (float) ($row['discount'] ?? 0));
            $discountType = $row['discount_type'] ?? 'fixed';
            $lineDiscount = $discountType === 'percentage'
                ? min($line, round($line * $requestedDiscount / 100, 2))
                : min($line, $requestedDiscount);
            $taxable = max(0, round($line - $lineDiscount, 2));
            $taxRate = max(0, min(100, (float) ($row['tax_rate'] ?? 0)));
            $taxAmount = round($taxable * $taxRate / 100, 2);
            $total = round($taxable + $taxAmount, 2);

            $subtotal += $line;
            $discount += $lineDiscount;
            $tax += $taxAmount;
            $normalized[] = [$row, $taxable, $taxAmount, $total, $lineDiscount, $idx];
        }

        $taxable = round($subtotal - $discount, 2);
        $grand = round(
            $taxable + $tax + (float) ($data['shipping_amount'] ?? 0)
            + (float) ($data['other_charges'] ?? 0)
            + (float) ($data['round_off'] ?? 0),
            2
        );

        if ($grand < 0) {
            throw ValidationException::withMessages(['items' => 'Invoice total cannot be negative.']);
        }

        return compact('subtotal', 'discount', 'taxable', 'tax', 'grand', 'normalized');
    }

    public function createInvoice(array $data, ?int $userId = null): Invoice
    {
        return DB::transaction(function () use ($data, $userId) {
            $customer = Customer::findOrFail($data['customer_id']);
            $this->validateOrderCustomer($data['order_id'] ?? null, $customer->id);
            $totals = $this->calculateTotals($data['items'], $data);

            $invoice = Invoice::create(array_merge([
                'invoice_number' => $this->generateInvoiceNumber(),
                'customer_id' => $customer->id,
                'order_id' => $data['order_id'] ?? null,
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? null,
                'status' => 'draft',
                'payment_status' => 'unpaid',
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'taxable_amount' => $totals['taxable'],
                'tax_amount' => $totals['tax'],
                'shipping_amount' => $data['shipping_amount'] ?? 0,
                'other_charges' => $data['other_charges'] ?? 0,
                'round_off' => $data['round_off'] ?? 0,
                'grand_total' => $totals['grand'],
                'paid_amount' => 0,
                'outstanding_amount' => $totals['grand'],
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
                'created_by' => $userId,
            ], $this->snapshotCustomer($customer)));

            foreach ($totals['normalized'] as [$row, $taxable, $taxAmount, $total, $discount, $idx]) {
                $invoice->items()->create([
                    'product_id' => $row['product_id'] ?? null,
                    'order_item_id' => $row['order_item_id'] ?? null,
                    'description' => $row['description'],
                    'quantity' => $row['quantity'],
                    'unit' => $row['unit'] ?? 'pcs',
                    'unit_price' => $row['unit_price'],
                    'discount' => $discount,
                    'discount_type' => $row['discount_type'] ?? 'fixed',
                    'taxable_amount' => $taxable,
                    'tax_rate' => $row['tax_rate'] ?? 0,
                    'tax_amount' => $taxAmount,
                    'total' => $total,
                    'sort_order' => $idx,
                ]);
            }

            return $invoice->load('items', 'customer', 'order');
        });
    }

    public function updateInvoice(Invoice $invoice, array $data, ?int $userId = null): Invoice
    {
        if ($invoice->status !== 'draft') {
            throw ValidationException::withMessages(['invoice' => 'Only draft invoices can be edited.']);
        }

        return DB::transaction(function () use ($invoice, $data, $userId) {
            $locked = Invoice::lockForUpdate()->findOrFail($invoice->id);
            $customerId = (int) ($data['customer_id'] ?? $locked->customer_id);
            $this->validateOrderCustomer($data['order_id'] ?? null, $customerId);
            $totals = $this->calculateTotals($data['items'], $data);

            $newCustomer = Customer::findOrFail($customerId);
            $locked->update(array_merge([
                'customer_id' => $customerId,
                'order_id' => $data['order_id'] ?? null,
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'taxable_amount' => $totals['taxable'],
                'tax_amount' => $totals['tax'],
                'shipping_amount' => $data['shipping_amount'] ?? 0,
                'other_charges' => $data['other_charges'] ?? 0,
                'round_off' => $data['round_off'] ?? 0,
                'grand_total' => $totals['grand'],
                'outstanding_amount' => max(0, $totals['grand'] - (float) $locked->paid_amount),
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
                'updated_by' => $userId,
            ], $this->snapshotCustomer($newCustomer)));

            $locked->items()->delete();
            foreach ($totals['normalized'] as [$row, $taxable, $taxAmount, $total, $discount, $idx]) {
                $locked->items()->create([
                    'product_id' => $row['product_id'] ?? null,
                    'order_item_id' => $row['order_item_id'] ?? null,
                    'description' => $row['description'],
                    'quantity' => $row['quantity'],
                    'unit' => $row['unit'] ?? 'pcs',
                    'unit_price' => $row['unit_price'],
                    'discount' => $discount,
                    'discount_type' => $row['discount_type'] ?? 'fixed',
                    'taxable_amount' => $taxable,
                    'tax_rate' => $row['tax_rate'] ?? 0,
                    'tax_amount' => $taxAmount,
                    'total' => $total,
                    'sort_order' => $idx,
                ]);
            }

            return $locked->fresh('items', 'customer', 'order');
        });
    }

    public function syncPaymentStatus(Invoice $invoice): Invoice
    {
        $invoice = Invoice::lockForUpdate()->findOrFail($invoice->id);
        $paid = (float) $invoice->paymentAllocations()
            ->whereNull('reversed_at')
            ->whereHas('payment', fn ($q) => $q->where('status', 'completed')->where('direction', 'received'))
            ->sum('allocated_amount');

        $grand = (float) $invoice->grand_total;
        $outstanding = max(0, round($grand - $paid, 2));
        $paymentStatus = $paid <= 0
            ? 'unpaid'
            : ($paid < $grand ? 'partially_paid' : ($paid > $grand ? 'overpaid' : 'paid'));

        $status = $invoice->status;
        if ($invoice->status !== 'cancelled' && $paymentStatus === 'paid') {
            $status = 'paid';
        } elseif ($invoice->status !== 'draft' && $invoice->status !== 'cancelled' && $paymentStatus !== 'paid' && $invoice->due_date?->lt(today())) {
            $status = 'overdue';
        } elseif ($invoice->status === 'paid' && $paymentStatus !== 'paid') {
            $status = 'sent';
        }

        $invoice->update([
            'paid_amount' => $paid,
            'outstanding_amount' => $outstanding,
            'payment_status' => $paymentStatus,
            'status' => $status,
        ]);

        return $invoice->fresh();
    }

    public function issue(Invoice $invoice, ?int $userId = null): Invoice
    {
        $invoice = Invoice::lockForUpdate()->findOrFail($invoice->id);
        if ($invoice->status !== 'draft') {
            throw ValidationException::withMessages(['invoice' => 'Only draft invoices can be issued.']);
        }
        $invoice->update(['status' => 'issued', 'updated_by' => $userId]);
        return $invoice->fresh();
    }

    public function sendInvoice(Invoice $invoice, ?int $userId = null): Invoice
    {
        $invoice = Invoice::lockForUpdate()->findOrFail($invoice->id);
        if (in_array($invoice->status, ['draft', 'cancelled'], true)) {
            throw ValidationException::withMessages(['invoice' => 'Only issued invoices can be sent.']);
        }
        $invoice->update(['status' => 'sent', 'updated_by' => $userId]);
        return $invoice->fresh();
    }

    public function createFromOrder(Order $order, ?int $userId = null): Invoice
    {
        if ($order->invoices()->whereNotIn('status', ['cancelled'])->exists()) {
            throw ValidationException::withMessages(['order' => 'An active invoice already exists for this order.']);
        }

        $order->loadMissing(['customer', 'items.product']);
        $items = $order->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'order_item_id' => $item->id,
            'description' => $item->description ?? ($item->product->name ?? 'Item'),
            'quantity' => $item->quantity,
            'unit' => $item->unit ?? 'pcs',
            'unit_price' => $item->unit_price ?? $item->price ?? 0,
            'discount' => 0,
            'discount_type' => 'fixed',
            'tax_rate' => 0,
        ])->values()->all();

        return $this->createInvoice([
            'customer_id' => $order->customer_id,
            'order_id' => $order->id,
            'invoice_date' => today()->format('Y-m-d'),
            'due_date' => null,
            'items' => $items,
            'shipping_amount' => 0,
            'other_charges' => 0,
            'round_off' => 0,
            'notes' => null,
            'terms' => null,
        ], $userId);
    }

    public function cancel(Invoice $invoice, string $reason, ?int $userId = null): Invoice
    {
        return DB::transaction(function () use ($invoice, $reason, $userId) {
            $invoice = Invoice::lockForUpdate()->findOrFail($invoice->id);
            $invoice = $this->syncPaymentStatus($invoice);
            if ((float) $invoice->paid_amount > 0) {
                throw ValidationException::withMessages(['invoice' => 'Invoice with payments cannot be cancelled until financial allocations are reversed.']);
            }
            if ($invoice->status === 'cancelled') {
                throw ValidationException::withMessages(['invoice' => 'Invoice is already cancelled.']);
            }

            $invoice->update([
                'status' => 'cancelled',
                'payment_status' => 'unpaid',
                'outstanding_amount' => 0,
                'cancelled_by' => $userId,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            return $invoice->fresh();
        });
    }

    public function duplicate(Invoice $invoice, ?int $userId = null): Invoice
    {
        $invoice->load('items');
        return DB::transaction(function () use ($invoice, $userId) {
            $new = $invoice->replicate();
            $new->invoice_number = $this->generateInvoiceNumber();
            $new->status = 'draft';
            $new->payment_status = 'unpaid';
            $new->paid_amount = 0;
            $new->outstanding_amount = $invoice->grand_total;
            $new->created_by = $userId;
            $new->updated_by = null;
            $new->cancelled_by = null;
            $new->cancelled_at = null;
            $new->cancellation_reason = null;
            $new->save();

            foreach ($invoice->items as $item) {
                $copy = $item->replicate();
                $copy->invoice_id = $new->id;
                $copy->save();
            }

            return $new->load('items', 'customer', 'order');
        });
    }

    private function validateOrderCustomer(?int $orderId, int $customerId): void
    {
        if (!$orderId) return;
        $order = Order::findOrFail($orderId);
        if ((int) $order->customer_id !== $customerId) {
            throw ValidationException::withMessages(['order_id' => 'Selected order does not belong to the selected customer.']);
        }
    }
}
