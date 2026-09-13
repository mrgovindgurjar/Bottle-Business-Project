<?php
namespace App\Policies;
use App\Models\Supplier;
use App\Models\User;
class SupplierPolicy
{
    public function viewAny(User $user): bool { return $user->hasPermission('suppliers.view'); }
    public function view(User $user, Supplier $supplier): bool { return $user->hasPermission('suppliers.view'); }
    public function create(User $user): bool { return $user->hasPermission('suppliers.create'); }
    public function update(User $user, Supplier $supplier): bool { return $user->hasPermission('suppliers.edit'); }
    public function delete(User $user, Supplier $supplier): bool { return $supplier->purchases()->exists() ? false : $user->hasPermission('suppliers.delete'); }
}
