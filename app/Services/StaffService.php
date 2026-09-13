<?php
namespace App\Services;

use App\Models\Staff;
use Illuminate\Support\Facades\DB;

class StaffService
{
    public function create(array $data): Staff
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['employee_code'])) {
                $data['employee_code'] = $this->nextEmployeeCode();
            }
            return Staff::create($data);
        });
    }

    public function update(Staff $staff, array $data): Staff
    {
        $staff->update($data);
        return $staff->refresh();
    }

    public function delete(Staff $staff): void
    {
        if ($staff->attendances()->exists()) {
            $staff->update(['status'=>'inactive']);
            return;
        }
        $staff->delete();
    }

    private function nextEmployeeCode(): string
    {
        $year = now()->format('Y');
        $prefix = 'EMP-'.$year.'-';
        $last = Staff::withTrashed()->where('employee_code','like',$prefix.'%')->orderByDesc('id')->value('employee_code');
        $n = $last && preg_match('/(\d+)$/', $last, $m) ? ((int)$m[1] + 1) : 1;
        return $prefix.str_pad((string)$n, 4, '0', STR_PAD_LEFT);
    }
}
