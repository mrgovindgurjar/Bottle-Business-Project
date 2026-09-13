<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $service) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermission('attendance.view'), 403);
        $date = $request->input('date', now()->toDateString());
        $staff = Staff::where('status','active')->orderBy('first_name')->orderBy('last_name')->get();
        $records = StaffAttendance::whereDate('attendance_date',$date)->get()->keyBy('staff_id');
        $summary = $this->service->summary($date,$date);
        return view('admin.attendance.index', compact('date','staff','records','summary'));
    }

    public function store(StoreAttendanceRequest $request)
    {
        $this->service->mark($request->validated(), $request->user()->id);
        return back()->with('success','Attendance saved successfully.');
    }

    public function bulkMark(Request $request)
    {
        abort_unless($request->user()->hasPermission('attendance.mark'), 403);
        $data = $request->validate([
            'attendance_date'=>['required','date'],
            'rows'=>['required','array'],
            'rows.*.status'=>['required','in:present,absent,late,half_day,leave,holiday'],
            'rows.*.check_in'=>['nullable','date_format:H:i'],
            'rows.*.check_out'=>['nullable','date_format:H:i'],
            'rows.*.remarks'=>['nullable','string','max:1000'],
        ]);
        $count = $this->service->bulkMark($data['rows'], Carbon::parse($data['attendance_date'])->toDateString(), $request->user()->id);
        return back()->with('success', $count.' attendance records saved.');
    }

    public function edit(Request $request, StaffAttendance $attendance)
    {
        abort_unless($request->user()->hasPermission('attendance.edit'), 403);
        $attendance->load('staff');
        return view('admin.attendance.edit', compact('attendance'));
    }

    public function update(UpdateAttendanceRequest $request, StaffAttendance $attendance)
    {
        $this->service->update($attendance,$request->validated());
        return redirect()->route('admin.attendance.index',['date'=>$attendance->attendance_date->toDateString()])->with('success','Attendance updated.');
    }

    public function history(Request $request, Staff $staff)
    {
        abort_unless($request->user()->hasPermission('attendance.view'), 403);
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());
        $attendance = $staff->attendances()->whereBetween('attendance_date',[$from,$to])->latest('attendance_date')->paginate(31)->withQueryString();
        $summary = $this->service->summary($from,$to);
        return view('admin.attendance.history', compact('staff','attendance','summary','from','to'));
    }
}
