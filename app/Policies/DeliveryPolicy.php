<?php

namespace App\Policies;

use App\Models\Delivery;
use App\Models\User;

class DeliveryPolicy
{
    private function allowed(User $user, string $permission): bool { return $user->hasPermission($permission); }
    private function any(User $user, array $permissions): bool { foreach ($permissions as $p) if ($user->hasPermission($p)) return true; return false; }

    public function viewAny(User $user): bool { return $this->allowed($user,'deliveries.view'); }
    public function view(User $user, Delivery $delivery): bool { return $this->allowed($user,'deliveries.view'); }
    public function create(User $user): bool { return $this->allowed($user,'deliveries.create'); }
    public function update(User $user, Delivery $delivery): bool { return $delivery->canEdit() && $this->allowed($user,'deliveries.edit'); }
    public function delete(User $user, Delivery $delivery): bool { return $delivery->status === 'draft' && $this->allowed($user,'deliveries.delete'); }
    public function dispatch(User $user, Delivery $delivery): bool { return $delivery->canDispatch() && $this->any($user,['deliveries.dispatch','deliveries.edit']); }
    public function deliver(User $user, Delivery $delivery): bool { return $delivery->canDeliver() && $this->allowed($user,'deliveries.deliver'); }
    public function fail(User $user, Delivery $delivery): bool { return in_array($delivery->status,['out_for_delivery','ready'],true) && $this->allowed($user,'deliveries.fail'); }
    public function cancel(User $user, Delivery $delivery): bool { return $delivery->canCancel() && $this->allowed($user,'deliveries.cancel'); }
    public function export(User $user, Delivery $delivery): bool { return $this->allowed($user,'deliveries.export'); }
}
