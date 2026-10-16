<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    private function can(User $user, string $permission): bool
    {
        return $user->hasPermission($permission);
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'payments.view');
    }

    public function view(User $user, Payment $payment): bool
    {
        if ($user->hasPermission('payments.view_all')) {
            return $this->can($user, 'payments.view');
        }

        if (!$this->can($user, 'payments.view')) {
            return false;
        }

        if ($payment->customer_id) {
            return (int) $payment->customer?->user_id === (int) $user->id;
        }

        // Supplier payments are internal finance records.
        return $user->hasPermission('payments.view_all');
    }

    public function create(User $user): bool { return $this->can($user, 'payments.create'); }

    public function update(User $user, Payment $payment): bool
    {
        return $this->can($user, 'payments.edit') && $payment->status === 'pending';
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $this->can($user, 'payments.delete') && !in_array($payment->status, ['cancelled', 'refunded'], true);
    }

    public function allocate(User $user, Payment $payment): bool
    {
        return $this->can($user, 'payments.allocate') && $payment->status === 'completed';
    }

    public function refund(User $user, Payment $payment): bool
    {
        return $this->can($user, 'payments.refund')
            && $payment->status === 'completed'
            && (float) $payment->amount > 0;
    }

    public function approve(User $user, Payment $payment): bool
    {
        return $this->can($user, 'payments.approve') && $payment->status === 'pending';
    }
}
