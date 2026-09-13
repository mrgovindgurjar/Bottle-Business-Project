<?php

namespace App\Policies;

use App\Models\Quotation;
use App\Models\User;

class QuotationPolicy
{
    private function can(User $user, string $permission): bool { return $user->hasPermission($permission); }
    public function viewAny(User $user): bool { return $this->can($user, 'quotations.view'); }
    public function view(User $user, Quotation $quotation): bool { return $this->can($user, 'quotations.view'); }
    public function create(User $user): bool { return $this->can($user, 'quotations.create'); }
    public function update(User $user, Quotation $quotation): bool { return $this->can($user, 'quotations.edit') && $quotation->isEditable(); }
    public function delete(User $user, Quotation $quotation): bool { return $this->can($user, 'quotations.delete') && !in_array($quotation->status, ['approved','converted'], true); }
    public function send(User $user, Quotation $quotation): bool { return $this->can($user, 'quotations.send') && in_array($quotation->status, ['draft','viewed'], true); }
    public function approve(User $user, Quotation $quotation): bool { return $this->can($user, 'quotations.approve') && in_array($quotation->status, ['sent','viewed'], true); }
    public function reject(User $user, Quotation $quotation): bool { return $this->can($user, 'quotations.reject') && in_array($quotation->status, ['sent','viewed'], true); }
    public function convert(User $user, Quotation $quotation): bool { return $this->can($user, 'quotations.convert') && $quotation->isConvertible(); }
}
