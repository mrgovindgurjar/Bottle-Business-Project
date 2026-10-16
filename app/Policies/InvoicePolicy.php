<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    private function can(User $user, string $permission): bool { return $user->hasPermission($permission); }
    public function viewAny(User $user): bool { return $this->can($user, 'invoices.view'); }
    public function view(User $user, Invoice $invoice): bool { return $this->can($user, 'invoices.view'); }
    public function create(User $user): bool { return $this->can($user, 'invoices.create'); }
    public function update(User $user, Invoice $invoice): bool { return $invoice->status === 'draft' && $this->can($user, 'invoices.edit'); }
    public function delete(User $user, Invoice $invoice): bool { return $invoice->status === 'draft' && $this->can($user, 'invoices.delete'); }
    public function issue(User $user, Invoice $invoice): bool { return $invoice->status === 'draft' && $this->can($user, 'invoices.issue'); }
    public function send(User $user, Invoice $invoice): bool { return !in_array($invoice->status, ['draft','cancelled'], true) && $this->can($user, 'invoices.send'); }
    public function cancel(User $user, Invoice $invoice): bool { return $invoice->status !== 'cancelled' && $this->can($user, 'invoices.cancel'); }
    public function duplicate(User $user, Invoice $invoice): bool { return $this->can($user, 'invoices.duplicate'); }
    public function export(User $user): bool { return $this->can($user, 'invoices.export'); }
    public function print(User $user, Invoice $invoice): bool { return $this->can($user, 'invoices.print'); }
    public function pdf(User $user, Invoice $invoice): bool { return $this->can($user, 'invoices.pdf'); }
}
