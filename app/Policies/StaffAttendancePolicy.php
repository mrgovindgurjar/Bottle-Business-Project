<?php
namespace App\Policies;
use App\Models\StaffAttendance;

class StaffAttendancePolicy
{
    public function viewAny($user): bool { return $user->hasPermission('attendance.view'); }
    public function view($user, StaffAttendance $attendance): bool { return $user->hasPermission('attendance.view'); }
    public function create($user): bool { return $user->hasPermission('attendance.mark'); }
    public function update($user, StaffAttendance $attendance): bool { return $user->hasPermission('attendance.edit'); }
    public function delete($user, StaffAttendance $attendance): bool { return $user->hasPermission('attendance.delete'); }
    public function export($user): bool { return $user->hasPermission('attendance.export'); }
}
