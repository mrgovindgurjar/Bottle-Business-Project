<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    private function allowed(User $user, string $permission): bool
    {
        return $user->hasPermission($permission);
    }

    private function allowedAny(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) return true;
        }
        return false;
    }

    public function viewAny(User $user): bool { return $this->allowed($user, 'orders.view'); }
    public function view(User $user, Order $order): bool { return $this->allowed($user, 'orders.view'); }
    public function create(User $user): bool { return $this->allowed($user, 'orders.create'); }
    public function update(User $user, Order $order): bool { return $order->isEditable() && $this->allowed($user, 'orders.edit'); }
    public function delete(User $user, Order $order): bool { return $order->isEditable() && $this->allowed($user, 'orders.delete'); }
    public function duplicate(User $user, Order $order): bool { return $this->allowed($user, 'orders.create'); }
    public function confirm(User $user, Order $order): bool { return $order->canConfirm() && $this->allowedAny($user, ['orders.confirm','orders.approve']); }
    public function changeStatus(User $user, Order $order): bool { return $this->allowedAny($user, ['orders.status','orders.approve']); }
    public function cancel(User $user, Order $order): bool { return $order->canCancel() && $this->allowed($user, 'orders.cancel'); }
    public function export(User $user, Order $order): bool { return $this->allowed($user, 'orders.export'); }
}
