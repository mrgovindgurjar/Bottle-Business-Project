<?php
namespace App\Policies;

use App\Models\InventoryItem;
use App\Models\User;

class InventoryItemPolicy
{
    public function viewAny(User $user): bool { return $user->hasPermission('inventory.view'); }
    public function view(User $user, InventoryItem $item): bool { return $user->hasPermission('inventory.view'); }
    public function create(User $user): bool { return $user->hasPermission('inventory.create'); }
    public function update(User $user, InventoryItem $item): bool { return $user->hasPermission('inventory.edit'); }
    public function delete(User $user, InventoryItem $item): bool { return $user->hasPermission('inventory.delete'); }
    public function adjust(User $user, InventoryItem $item): bool { return $user->hasPermission('inventory.adjust'); }
    public function reserve(User $user, InventoryItem $item): bool { return $user->hasPermission('inventory.reserve'); }
    public function transfer(User $user, InventoryItem $item): bool { return $user->hasPermission('inventory.transfer'); }
    public function export(User $user): bool { return $user->hasPermission('inventory.export'); }
}
