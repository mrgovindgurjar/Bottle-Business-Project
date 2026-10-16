<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadActivityRequest;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Lead;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class LeadController extends Controller
{
    public function __construct(protected LeadService $leadService)
    {
    }

    protected function salesUsers()
    {
        return User::query()
            ->where('is_active', true)
            ->whereDoesntHave('roles', fn ($q) => $q->where('slug', 'customer'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Lead::class);

        $query = Lead::query()->with(['assignedUser:id,name', 'convertedCustomer:id,customer_code,business_name'])
            ->search($request->input('search'));

        foreach (['status', 'source', 'business_type', 'assigned_to'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('followup_from')) {
            $query->whereDate('next_followup_at', '>=', $request->input('followup_from'));
        }
        if ($request->filled('followup_to')) {
            $query->whereDate('next_followup_at', '<=', $request->input('followup_to'));
        }

        $leads = $query->latest('id')->paginate(15)->withQueryString();
        $salesUsers = $this->salesUsers();

        $stats = [
            'total' => Lead::count(),
            'new' => Lead::where('status', Lead::STATUS_NEW)->count(),
            'followups' => Lead::whereNotNull('next_followup_at')->whereDate('next_followup_at', today())
                ->whereNotIn('status', [Lead::STATUS_WON, Lead::STATUS_LOST])->count(),
            'won' => Lead::where('status', Lead::STATUS_WON)->count(),
        ];

        $sources = Lead::query()->whereNotNull('source')->where('source', '!=', '')->distinct()->orderBy('source')->pluck('source');
        $businessTypes = Lead::query()->whereNotNull('business_type')->where('business_type', '!=', '')->distinct()->orderBy('business_type')->pluck('business_type');

        return view('admin.leads.index', compact('leads', 'salesUsers', 'stats', 'sources', 'businessTypes'));
    }

    public function create()
    {
        Gate::authorize('create', Lead::class);
        return view('admin.leads.create', ['salesUsers' => $this->salesUsers()]);
    }

    public function store(StoreLeadRequest $request)
    {
        Gate::authorize('create', Lead::class);
        $lead = $this->leadService->create($request->validated(), $request->user());
        return redirect()->route('admin.leads.show', $lead)->with('success', 'Lead created successfully.');
    }

    public function show(Lead $lead)
    {
        Gate::authorize('view', $lead);
        $lead->load(['assignedUser', 'convertedCustomer', 'activities.user']);
        return view('admin.leads.show', ['lead' => $lead, 'salesUsers' => $this->salesUsers()]);
    }

    public function edit(Lead $lead)
    {
        Gate::authorize('update', $lead);
        return view('admin.leads.edit', ['lead' => $lead, 'salesUsers' => $this->salesUsers()]);
    }

    public function update(UpdateLeadRequest $request, Lead $lead)
    {
        Gate::authorize('update', $lead);
        $data = $request->validated();
        if (array_key_exists('assigned_to', $data) && (int) $data['assigned_to'] !== (int) $lead->assigned_to) {
            Gate::authorize('assign', $lead);
        }
        $this->leadService->update($lead, $data, $request->user());
        return redirect()->route('admin.leads.show', $lead)->with('success', 'Lead updated successfully.');
    }

    public function destroy(Lead $lead)
    {
        Gate::authorize('delete', $lead);
        $this->leadService->delete($lead, request()->user());
        return redirect()->route('admin.leads.index')->with('success', 'Lead moved to trash.');
    }

    public function activity(StoreLeadActivityRequest $request, Lead $lead)
    {
        Gate::authorize('activity', $lead);
        $data = $request->validated();
        $this->leadService->addActivity($lead, $request->user(), $data['type'], $data['subject'] ?? ucfirst($data['type']), $data['description'], $data['activity_at'], $data['next_followup_at'] ?? null);
        return back()->with('success', 'Activity added successfully.');
    }

    public function status(Request $request, Lead $lead)
    {
        Gate::authorize('status', $lead);
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', Lead::STATUSES)],
            'lost_reason' => ['nullable', 'string', 'max:2000'],
        ]);
        $this->leadService->changeStatus($lead, $data['status'], $request->user(), $data['lost_reason'] ?? null);
        return back()->with('success', 'Lead status updated.');
    }

    public function convert(Request $request, Lead $lead)
    {
        Gate::authorize('convert', $lead);
        $customer = $this->leadService->convertToCustomer($lead, $request->user());
        return redirect()->route('admin.leads.show', $lead)
            ->with('success', "Lead converted successfully. Customer: {$customer->customer_code}");
    }

    public function export(Request $request): Response
    {
        Gate::authorize('export', Lead::class);

        $leads = Lead::with('assignedUser:id,name')
            ->search($request->input('search'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('source'), fn ($q) => $q->where('source', $request->input('source')))
            ->orderBy('id')->get();

        $filename = 'leads-' . now()->format('Y-m-d-His') . '.csv';
        return response()->stream(function () use ($leads) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Lead Code', 'Business', 'Contact', 'Mobile', 'Email', 'Source', 'Assigned To', 'Status', 'Follow-up', 'Estimated Quantity', 'Estimated Value', 'Created At']);
            foreach ($leads as $lead) {
                fputcsv($out, [
                    $lead->lead_code, $lead->business_name, $lead->contact_name, $lead->mobile, $lead->email,
                    $lead->source, $lead->assignedUser?->name, $lead->status, optional($lead->next_followup_at)->format('Y-m-d H:i:s'),
                    $lead->estimated_quantity, $lead->estimated_value, optional($lead->created_at)->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($out);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
