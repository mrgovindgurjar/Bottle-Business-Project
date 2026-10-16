<?php

namespace App\Services;

use App\Models\{Invoice, Payment, PaymentAllocation};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PaymentService
{
    public function generatePaymentNumber(): string
    {
        return $this->nextSequence('payment:' . now()->year, 'PAY-' . now()->year . '-', 6);
    }

    public function generateReceiptNumber(): string
    {
        return $this->nextSequence('receipt:' . now()->format('Ym'), 'RCP-' . now()->format('Ym') . '-', 6);
    }

    private function nextSequence(string $key, string $prefix, int $width): string
    {
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
            DB::table('finance_sequences')->where('id', $row->id)->update(['current_number' => $number, 'updated_at' => now()]);
        }
        return $prefix . str_pad((string) $number, $width, '0', STR_PAD_LEFT);
    }

    public function createPayment(array $data, ?int $userId = null): Payment
    {
        return DB::transaction(function () use ($data, $userId) {
            $this->validateParty($data);
            $data['payment_number'] = $this->generatePaymentNumber();
            $data['status'] = $data['status'] ?? 'completed';
            $data['created_by'] = $userId;
            $data['received_by'] = $data['direction'] === 'received' ? $userId : null;
            $data['paid_by'] = $data['direction'] === 'paid' ? $userId : null;
            $data['receipt_number'] = $this->generateReceiptNumber();
            $allocations = $data['allocations'] ?? [];
            unset($data['allocations']);

            $payment = Payment::create($data);
            if ($allocations) {
                $this->allocatePayment($payment, $allocations, $userId);
            }
            return $payment->fresh(['allocations']);
        });
    }

    private function validateParty(array $data): void
    {
        $customer = !empty($data['customer_id']);
        $supplier = !empty($data['supplier_id']);
        if ($customer === $supplier) {
            throw ValidationException::withMessages(['customer_id' => 'Exactly one of customer or supplier is required.']);
        }
        if (($data['direction'] ?? null) === 'received' && !$customer) {
            throw ValidationException::withMessages(['customer_id' => 'Received payments must belong to a customer.']);
        }
        if (($data['direction'] ?? null) === 'paid' && !$supplier) {
            throw ValidationException::withMessages(['supplier_id' => 'Paid transactions must belong to a supplier.']);
        }
    }

    public function calculateUnallocated(Payment $payment): float
    {
        return max(0, round((float) $payment->amount - (float) $payment->allocations()->whereNull('reversed_at')->sum('allocated_amount'), 2));
    }

    public function allocatePayment(Payment $payment, array $rows, ?int $userId = null): void
    {
        DB::transaction(function () use ($payment, $rows, $userId) {
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);
            if ($payment->status !== 'completed' || $payment->direction !== 'received') {
                throw new RuntimeException('Only completed received payments can be allocated to customer receivables.');
            }

            $remaining = $this->calculateUnallocated($payment);
            $invoiceIds = [];

            foreach ($rows as $row) {
                $amount = (float) ($row['amount'] ?? 0);
                if ($amount <= 0) continue;
                if ($amount > $remaining + 0.0001) {
                    throw new RuntimeException('Allocation exceeds payment unallocated amount.');
                }

                $targetKeys = array_filter([
                    'invoice_id' => $row['invoice_id'] ?? null,
                    'order_id' => $row['order_id'] ?? null,
                    'purchase_id' => $row['purchase_id'] ?? null,
                ]);
                if (count($targetKeys) !== 1) {
                    throw new RuntimeException('Each allocation must target exactly one document.');
                }

                if (!empty($row['invoice_id'])) {
                    $invoice = Invoice::lockForUpdate()->findOrFail((int) $row['invoice_id']);
                    if ($invoice->status === 'cancelled') {
                        throw new RuntimeException('Cancelled invoices cannot receive payment allocations.');
                    }
                    if ((int) $invoice->customer_id !== (int) $payment->customer_id) {
                        throw new RuntimeException('Invoice customer does not match the payment customer.');
                    }
                    $outstanding = $this->invoiceOutstanding($invoice);
                    if ($amount > $outstanding + 0.0001) {
                        throw new RuntimeException('Allocation exceeds invoice outstanding amount.');
                    }
                    $invoiceIds[] = $invoice->id;
                } elseif (!empty($row['order_id'])) {
                    $order = \App\Models\Order::lockForUpdate()->findOrFail((int) $row['order_id']);
                    if ((int) $order->customer_id !== (int) $payment->customer_id) {
                        throw new RuntimeException('Order customer does not match the payment customer.');
                    }
                    $outstanding = $this->documentOutstanding((int) $order->id, 'order_id', $order->grand_total ?? $order->total ?? 0);
                    if ($amount > $outstanding + 0.0001) throw new RuntimeException('Allocation exceeds order outstanding amount.');
                } else {
                    throw new RuntimeException('Supplier purchase allocations are not allowed on received customer payments.');
                }

                PaymentAllocation::create([
                    'payment_id' => $payment->id,
                    'invoice_id' => $row['invoice_id'] ?? null,
                    'order_id' => $row['order_id'] ?? null,
                    'purchase_id' => $row['purchase_id'] ?? null,
                    'allocated_amount' => $amount,
                    'allocation_date' => now()->toDateString(),
                    'notes' => $row['notes'] ?? null,
                ]);
                $remaining = round($remaining - $amount, 2);
            }

            foreach (array_unique($invoiceIds) as $invoiceId) {
                app(InvoiceService::class)->syncPaymentStatus(Invoice::findOrFail($invoiceId));
            }
        });
    }

    private function invoiceOutstanding(Invoice $invoice): float
    {
        $paid = (float) $invoice->paymentAllocations()
            ->whereNull('reversed_at')
            ->whereHas('payment', fn ($q) => $q->where('status', 'completed')->where('direction', 'received'))
            ->sum('allocated_amount');
        return max(0, round((float) $invoice->grand_total - $paid, 2));
    }

    private function documentOutstanding(int $id, string $column, float $total): float
    {
        $paid = (float) PaymentAllocation::whereNull('reversed_at')
            ->where($column, $id)
            ->whereHas('payment', fn ($q) => $q->where('status', 'completed'))
            ->sum('allocated_amount');
        return max(0, round($total - $paid, 2));
    }

    public function removeAllocation(PaymentAllocation $allocation, ?int $userId = null, string $reason = 'Manual reversal'): void
    {
        DB::transaction(function () use ($allocation, $userId, $reason) {
            $allocation = PaymentAllocation::lockForUpdate()->findOrFail($allocation->id);
            if ($allocation->reversed_at) throw new RuntimeException('Allocation already reversed.');
            $invoiceId = $allocation->invoice_id;
            $allocation->update(['reversed_at' => now(), 'reversed_by' => $userId, 'reversal_reason' => $reason]);
            if ($invoiceId) app(InvoiceService::class)->syncPaymentStatus(Invoice::findOrFail($invoiceId));
        });
    }

    public function refundPayment(Payment $payment, array $data, ?int $userId = null): Payment
    {
        return DB::transaction(function () use ($payment, $data, $userId) {
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);
            $activeAllocations = $payment->allocations()->whereNull('reversed_at')->count();
            if ($activeAllocations > 0) {
                throw new RuntimeException('Reverse the payment allocations before refunding this payment.');
            }
            $refunded = (float) $payment->refunds()->whereIn('status', ['completed', 'partially_refunded'])->sum('amount');
            $amount = (float) $data['amount'];
            if ($refunded + $amount > (float) $payment->amount + 0.0001) throw new RuntimeException('Refund exceeds refundable amount.');

            $data['direction'] = $payment->direction === 'received' ? 'paid' : 'received';
            $data['payment_type'] = 'refund';
            $data['customer_id'] = $payment->customer_id;
            $data['supplier_id'] = $payment->supplier_id;
            $data['order_id'] = $payment->order_id;
            $data['purchase_id'] = $payment->purchase_id;
            $data['invoice_id'] = $payment->invoice_id;
            $data['refund_of_payment_id'] = $payment->id;
            $data['status'] = 'completed';
            $data['created_by'] = $userId;
            $data['payment_number'] = $this->generatePaymentNumber();
            $data['receipt_number'] = $this->generateReceiptNumber();
            $data['received_by'] = $data['direction'] === 'received' ? $userId : null;
            $data['paid_by'] = $data['direction'] === 'paid' ? $userId : null;
            $refund = Payment::create($data);
            $newRefunded = $refunded + $amount;
            $payment->update(['status' => $newRefunded >= (float) $payment->amount ? 'refunded' : 'partially_refunded']);
            return $refund;
        });
    }

    public function cancelPayment(Payment $payment, ?int $userId = null): void
    {
        DB::transaction(function () use ($payment, $userId) {
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);
            if (in_array($payment->status, ['refunded', 'cancelled'], true)) throw new RuntimeException('This payment cannot be cancelled.');
            $invoiceIds = $payment->allocations()->whereNull('reversed_at')->pluck('invoice_id')->filter()->unique();
            $payment->allocations()->whereNull('reversed_at')->update(['reversed_at' => now(), 'reversed_by' => $userId, 'reversal_reason' => 'Payment cancelled']);
            $payment->update(['status' => 'cancelled']);
            foreach ($invoiceIds as $invoiceId) app(InvoiceService::class)->syncPaymentStatus(Invoice::findOrFail($invoiceId));
        });
    }

    public function approvePayment(Payment $payment, ?int $userId = null): void
    {
        $payment->update(['status' => 'completed', 'approved_by' => $userId, 'approved_at' => now()]);
    }

    public function getCustomerLedger(int $customerId, ?string $from = null, ?string $to = null)
    {
        return $this->ledger(Payment::where('customer_id', $customerId), $from, $to);
    }

    public function getSupplierLedger(int $supplierId, ?string $from = null, ?string $to = null)
    {
        return $this->ledger(Payment::where('supplier_id', $supplierId), $from, $to);
    }

    private function ledger(Builder $query, ?string $from, ?string $to)
    {
        $rows = [];
        $payments = $query->whereIn('status', ['completed', 'partially_refunded', 'refunded'])
            ->when($from, fn ($q) => $q->whereDate('payment_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('payment_date', '<=', $to))
            ->orderBy('payment_date')->orderBy('id')->get();
        foreach ($payments as $payment) {
            $rows[] = [
                'date' => $payment->payment_date,
                'reference' => $payment->payment_number,
                'type' => $payment->payment_type,
                'debit' => $payment->direction === 'paid' ? (float) $payment->amount : 0,
                'credit' => $payment->direction === 'received' ? (float) $payment->amount : 0,
            ];
        }
        return collect($rows);
    }

    public function query(array $filters = []): Builder
    {
        $q = Payment::query()->with(['customer', 'supplier', 'order', 'purchase', 'invoice', 'creator', 'allocations']);
        $q->when($filters['search'] ?? null, function ($q, $s) {
            $q->where(fn ($q) => $q->where('payment_number', 'like', "%$s%")
                ->orWhere('reference_number', 'like', "%$s%")
                ->orWhereHas('customer', fn ($q) => $q->where('business_name', 'like', "%$s%"))
                ->orWhereHas('supplier', fn ($q) => $q->where('business_name', 'like', "%$s%")));
        });
        foreach (['direction', 'status', 'method', 'payment_type'] as $field) {
            $q->when($filters[$field] ?? null, fn ($q, $value) => $q->where($field, $value));
        }
        $q->when($filters['from'] ?? null, fn ($q, $value) => $q->whereDate('payment_date', '>=', $value));
        $q->when($filters['to'] ?? null, fn ($q, $value) => $q->whereDate('payment_date', '<=', $value));
        return $q->latest('id');
    }
}
