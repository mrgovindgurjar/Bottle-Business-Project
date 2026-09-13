<?php
namespace App\Policies;
use App\Models\Staff;

class StaffPolicy
{
    public function viewAny($user): bool { return $user->hasPermission('staff.view'); }
    public function view($user, Staff $staff): bool { return $user->hasPermission('staff.view'); }
    public function create($user): bool { return $user->hasPermission('staff.create'); }
    public function update($user, Staff $staff): bool { return $user->hasPermission('staff.edit'); }
    public function delete($user, Staff $staff): bool { return $user->hasPermission('staff.delete'); }
}
