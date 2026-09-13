<?php
namespace App\Policies;
use App\Models\Purchase;
use App\Models\User;
class PurchasePolicy
{
    public function viewAny(User $user): bool { return $user->hasPermission('purchases.view'); }
    public function view(User $user, Purchase $purchase): bool { return $user->hasPermission('purchases.view'); }
    public function create(User $user): bool { return $user->hasPermission('purchases.create'); }
    public function update(User $user, Purchase $purchase): bool { return in_array($purchase->status,['draft','ordered'],true) && $user->hasPermission('purchases.edit'); }
    public function delete(User $user, Purchase $purchase): bool { return $purchase->status==='draft' && $user->hasPermission('purchases.delete'); }
    public function receive(User $user, Purchase $purchase): bool { return !in_array($purchase->status,['cancelled','received'],true) && $user->hasPermission('purchases.receive'); }
    public function pay(User $user, Purchase $purchase): bool { return (float)$purchase->balance_amount > 0 && $user->hasPermission('purchases.pay'); }
    public function cancel(User $user, Purchase $purchase): bool { return !in_array($purchase->status,['received','cancelled'],true) && $user->hasPermission('purchases.cancel'); }
    public function export(User $user, Purchase $purchase): bool { return $user->hasPermission('purchases.export'); }
}
