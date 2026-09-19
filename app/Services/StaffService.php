<?php
namespace App\Services;

use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class StaffService
{
    public function create(array $data): Staff
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['employee_code'])) $data['employee_code'] = $this->nextEmployeeCode();
            $roleId = $data['role_id']; unset($data['role_id']);
            $user = User::create([
                'name' => trim($data['first_name'].' '.($data['last_name'] ?? '')),
                'email' => $data['email'],
                'mobile' => $data['mobile'],
                'password' => Hash::make('123456'),
                'is_active' => ($data['status'] ?? 'active') === 'active',
            ]);
            $role = Role::whereKey($roleId)->where('is_active',true)->first();
            if (!$role) throw ValidationException::withMessages(['role_id'=>'Selected role is not active.']);
            $user->roles()->sync([$role->id]);
            $data['user_id'] = $user->id;
            return Staff::create($data);
        });
    }

    public function update(Staff $staff, array $data): Staff
    {
        return DB::transaction(function () use ($staff,$data) {
            $roleId=$data['role_id']; unset($data['role_id']);
            $staff->update($data);
            if ($staff->user) {
                $staff->user->update([
                    'name'=>trim($data['first_name'].' '.($data['last_name'] ?? '')),
                    'email'=>$data['email'],'mobile'=>$data['mobile'],'is_active'=>($data['status'] ?? 'active')==='active',
                ]);
                $role=Role::whereKey($roleId)->where('is_active',true)->first();
                if (!$role) throw ValidationException::withMessages(['role_id'=>'Selected role is not active.']);
                $staff->user->roles()->sync([$role->id]);
            }
            return $staff->refresh();
        });
    }

    public function delete(Staff $staff): void
    {
        if ($staff->attendances()->exists() || $staff->salaryPayments()->exists()) { $staff->update(['status'=>'inactive']); if($staff->user) $staff->user->update(['is_active'=>false]); return; }
        DB::transaction(function() use($staff){ if($staff->user) $staff->user->update(['is_active'=>false]); $staff->delete(); });
    }

    private function nextEmployeeCode(): string
    {
        $year=now()->format('Y'); $prefix='EMP-'.$year.'-';
        $last=Staff::withTrashed()->where('employee_code','like',$prefix.'%')->orderByDesc('id')->value('employee_code');
        $n=$last&&preg_match('/(\d+)$/',$last,$m)?((int)$m[1]+1):1;
        return $prefix.str_pad((string)$n,4,'0',STR_PAD_LEFT);
    }
}
