<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIssueCategoryRequest;
use App\Http\Requests\StoreIssueCommentRequest;
use App\Http\Requests\StoreIssueRequest;
use App\Http\Requests\UpdateIssueCategoryRequest;
use App\Http\Requests\UpdateIssueRequest;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Issue;
use App\Models\IssueCategory;
use App\Models\Order;
use App\Models\Staff;
use App\Services\IssueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class IssueController extends Controller
{
    public function __construct(private IssueService $service) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->hasPermission('issues.view'),403);
        $query = Issue::query()->with(['category','customer','assignee']);
        $query->search($request->string('search')->toString());
        foreach (['status','priority','category_id','assigned_to','customer_id'] as $filter) if ($request->filled($filter)) $query->where($filter,$request->input($filter));
        if ($request->boolean('overdue')) $query->whereNotNull('due_date')->whereDate('due_date','<',today())->whereNotIn('status',['resolved','closed','cancelled']);
        $issues = $query->latest('id')->paginate(20)->withQueryString();
        $stats = [
            'open'=>Issue::whereIn('status',['open','in_progress','pending_customer'])->count(),
            'urgent'=>Issue::where('priority','urgent')->whereNotIn('status',['resolved','closed','cancelled'])->count(),
            'overdue'=>Issue::whereNotNull('due_date')->whereDate('due_date','<',today())->whereNotIn('status',['resolved','closed','cancelled'])->count(),
            'resolved_month'=>Issue::whereIn('status',['resolved','closed'])->whereBetween('resolved_at',[now()->startOfMonth(),now()->endOfMonth()])->count(),
        ];
        return view('admin.issues.index', compact('issues','stats'))->with([
            'categories'=>IssueCategory::active()->orderBy('sort_order')->orderBy('name')->get(),
            'staff'=>Staff::where('status','active')->orderBy('first_name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->hasPermission('issues.create'),403);
        return view('admin.issues.create', $this->formData());
    }

    public function store(StoreIssueRequest $request): RedirectResponse
    {
        $issue = $this->service->create($request->validated() + ['attachment_files'=>$request->file('attachment_files')], $request->user());
        return redirect()->route('admin.issues.show',$issue)->with('success','Issue created successfully.');
    }

    public function show(Request $request, Issue $issue): View
    {
        abort_unless($request->user()->hasPermission('issues.view'),403);
        $issue->load(['category','customer','order','delivery','batch','assignee','creator','updater','comments.user','activities.user']);
        return view('admin.issues.show', array_merge(compact('issue'), $this->formData()));
    }

    public function edit(Request $request, Issue $issue): View
    {
        abort_unless($request->user()->hasPermission('issues.edit'),403);
        return view('admin.issues.edit', array_merge(compact('issue'), $this->formData()));
    }

    public function update(UpdateIssueRequest $request, Issue $issue): RedirectResponse
    {
        $this->service->update($issue,$request->validated()+['attachment_files'=>$request->file('attachment_files')],$request->user());
        return redirect()->route('admin.issues.show',$issue)->with('success','Issue updated successfully.');
    }

    public function destroy(Request $request, Issue $issue): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('issues.delete'),403);
        $this->service->delete($issue);
        return redirect()->route('admin.issues.index')->with('success','Issue archived.');
    }

    public function status(Request $request, Issue $issue): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('issues.status'),403);
        $request->validate(['status'=>'required|in:open,in_progress,pending_customer,resolved,closed,cancelled','message'=>'nullable|string|max:1000']);
        $this->service->changeStatus($issue,$request->status,$request->user(),$request->message);
        return back()->with('success','Issue status updated.');
    }

    public function assign(Request $request, Issue $issue): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('issues.assign'),403);
        $request->validate(['assigned_to'=>'nullable|exists:staff,id']);
        $this->service->assign($issue,$request->integer('assigned_to') ?: null,$request->user());
        return back()->with('success','Issue assignment updated.');
    }

    public function comment(StoreIssueCommentRequest $request, Issue $issue): RedirectResponse
    {
        $this->service->addComment($issue,$request->validated()+['attachments'=>$request->file('attachments')],$request->user());
        return back()->with('success','Comment added.');
    }

    public function categories(Request $request): View
    {
        abort_unless($request->user()->hasPermission('issues.categories'),403);
        $categories = IssueCategory::withCount('issues')->orderBy('sort_order')->orderBy('name')->paginate(20);
        return view('admin.issues.categories.index',compact('categories'));
    }

    public function categoryStore(StoreIssueCategoryRequest $request): RedirectResponse
    {
        $data=$request->validated(); $data['slug']=$data['slug'] ?: Str::slug($data['name']);
        IssueCategory::create($data); return back()->with('success','Issue category created.');
    }

    public function categoryUpdate(UpdateIssueCategoryRequest $request, IssueCategory $category): RedirectResponse
    {
        $data=$request->validated(); $data['slug']=$data['slug'] ?: Str::slug($data['name']);
        $category->update($data); return back()->with('success','Issue category updated.');
    }

    public function categoryToggle(Request $request, IssueCategory $category): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('issues.categories'),403);
        $category->update(['is_active'=>!$category->is_active]); return back()->with('success','Category status updated.');
    }

    private function formData(): array
    {
        return [
            'categories'=>IssueCategory::active()->orderBy('sort_order')->orderBy('name')->get(),
            'staff'=>Staff::where('status','active')->orderBy('first_name')->orderBy('last_name')->get(),
            'customers'=>Customer::orderBy('business_name')->limit(500)->get(),
            'orders'=>class_exists(Order::class) ? Order::query()->latest('id')->limit(500)->get() : collect(),
            'deliveries'=>class_exists(Delivery::class) ? Delivery::query()->latest('id')->limit(500)->get() : collect(),
            'batches'=>class_exists(Batch::class) ? Batch::query()->latest('id')->limit(500)->get() : collect(),
        ];
    }
}
