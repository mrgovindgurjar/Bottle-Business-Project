<?php
namespace App\Services;

use App\Models\Staff;
use App\Models\StaffAttendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function mark(array $data, int $userId): StaffAttendance
    {
        return DB::transaction(function () use ($data, $userId) {
            $staff = Staff::lockForUpdate()->findOrFail($data['staff_id']);
            $date = Carbon::parse($data['attendance_date'])->toDateString();
            $values = $this->calculate($data);
            return StaffAttendance::updateOrCreate(
                ['staff_id'=>$staff->id,'attendance_date'=>$date],
                array_merge($values, ['marked_by'=>$userId])
            );
        });
    }

    public function bulkMark(array $rows, string $date, int $userId): int
    {
        $count = 0;
        DB::transaction(function () use ($rows, $date, $userId, &$count) {
            foreach ($rows as $staffId => $row) {
                $staff = Staff::whereKey($staffId)->lockForUpdate()->first();
                if (!$staff || $staff->status !== 'active') continue;
                $values = $this->calculate($row);
                StaffAttendance::updateOrCreate(
                    ['staff_id'=>$staff->id,'attendance_date'=>$date],
                    array_merge($values, ['marked_by'=>$userId])
                );
                $count++;
            }
        });
        return $count;
    }

    public function update(StaffAttendance $attendance, array $data): StaffAttendance
    {
        $attendance->update($this->calculate($data));
        return $attendance->refresh();
    }

    public function calculate(array $data): array
    {
        $status = $data['status'] ?? 'present';
        $checkIn = $data['check_in'] ?? null;
        $checkOut = $data['check_out'] ?? null;
        $workMinutes = 0;
        if ($checkIn && $checkOut) {
            try {
                $start = Carbon::createFromFormat('H:i', $checkIn);
                $end = Carbon::createFromFormat('H:i', $checkOut);
                if ($end->lessThan($start)) $end->addDay();
                $workMinutes = max(0, $start->diffInMinutes($end));
            } catch (\Throwable $e) { $workMinutes = 0; }
        }
        $lateMinutes = (int)($data['late_minutes'] ?? 0);
        if ($status === 'late' && $lateMinutes === 0 && $checkIn) {
            try {
                $shift = Carbon::createFromFormat('H:i', '09:30');
                $in = Carbon::createFromFormat('H:i', $checkIn);
                if ($in->greaterThan($shift)) $lateMinutes = $shift->diffInMinutes($in);
            } catch (\Throwable $e) {}
        }
        return [
            'status'=>$status,
            'check_in'=>$checkIn,
            'check_out'=>$checkOut,
            'late_minutes'=>$lateMinutes,
            'work_minutes'=>$workMinutes,
            'overtime_minutes'=>(int)($data['overtime_minutes'] ?? 0),
            'remarks'=>$data['remarks'] ?? null,
        ];
    }

    public function summary(string $from, string $to): array
    {
        $rows = StaffAttendance::whereBetween('attendance_date', [$from,$to])->get();
        return [
            'present'=>$rows->where('status','present')->count(),
            'late'=>$rows->where('status','late')->count(),
            'absent'=>$rows->where('status','absent')->count(),
            'half_day'=>$rows->where('status','half_day')->count(),
            'leave'=>$rows->where('status','leave')->count(),
            'holiday'=>$rows->where('status','holiday')->count(),
        ];
    }
}
