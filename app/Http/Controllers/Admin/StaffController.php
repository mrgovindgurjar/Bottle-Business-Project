<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Models\Staff;
use App\Models\Role;
use App\Services\StaffService;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function __construct(private StaffService $service) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermission('staff.view'), 403);
        $staff = Staff::query()->search($request->input('q'))
            ->when($request->filled('status'), fn($q)=>$q->where('status',$request->status))
            ->when($request->filled('department'), fn($q)=>$q->where('department',$request->department))
            ->latest('id')->paginate(20)->withQueryString();
        $departments = Staff::query()->whereNotNull('department')->where('department','!=','')->distinct()->orderBy('department')->pluck('department');
        $stats = [
            'total'=>Staff::count(), 'active'=>Staff::where('status','active')->count(),
            'on_leave'=>Staff::where('status','on_leave')->count(), 'inactive'=>Staff::whereIn('status',['inactive','terminated'])->count(),
        ];
        return view('admin.staff.index', compact('staff','departments','stats'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->hasPermission('staff.create'), 403);
        return view('admin.staff.create', ['roles' => Role::where('is_active', true)->orderBy('name')->get(['id','name'])]);
    }

    public function store(StoreStaffRequest $request)
    {
        $staff = $this->service->create($request->validated());
        return redirect()->route('admin.staff.show',$staff)->with('success','Staff member created successfully.');
    }

    public function show(Request $request, Staff $staff)
    {
        abort_unless($request->user()->hasPermission('staff.view'), 403);
        $staff->load(['attendances'=>fn($q)=>$q->latest('attendance_date')->limit(31)]);
        $attendanceStats = $staff->attendances()->selectRaw("status, COUNT(*) as total")->groupBy('status')->pluck('total','status');
        return view('admin.staff.show', compact('staff','attendanceStats'));
    }

    public function edit(Request $request, Staff $staff)
    {
        abort_unless($request->user()->hasPermission('staff.edit'), 403);
        return view('admin.staff.edit', ['staff'=>$staff, 'roles'=>Role::where('is_active', true)->orderBy('name')->get(['id','name'])]);
    }

    public function update(UpdateStaffRequest $request, Staff $staff)
    {
        $this->service->update($staff, $request->validated());
        return redirect()->route('admin.staff.show',$staff)->with('success','Staff member updated successfully.');
    }

    public function destroy(Request $request, Staff $staff)
    {
        abort_unless($request->user()->hasPermission('staff.delete'), 403);
        $this->service->delete($staff);
        return redirect()->route('admin.staff.index')->with('success','Staff member removed/deactivated successfully.');
    }
}
