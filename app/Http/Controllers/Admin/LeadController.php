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

class LeadController extends Controller
{
    public function __construct(
        protected LeadService $leadService
    ) {
    }


    public function index(Request $request)
    {
 

        $query = Lead::query()
            ->with([
                'assignedUser:id,name',
                'convertedCustomer:id,customer_code,business_name',
            ]);


        $query->search(
            $request->input('search')
        );


        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );
        }


        if ($request->filled('source')) {

            $query->where(
                'source',
                $request->source
            );
        }


        if ($request->filled('business_type')) {

            $query->where(
                'business_type',
                $request->business_type
            );
        }


        if ($request->filled('assigned_to')) {

            $query->where(
                'assigned_to',
                $request->assigned_to
            );
        }


        if (
            $request->filled('followup_from')
        ) {

            $query->whereDate(
                'next_followup_at',
                '>=',
                $request->followup_from
            );
        }


        if (
            $request->filled('followup_to')
        ) {

            $query->whereDate(
                'next_followup_at',
                '<=',
                $request->followup_to
            );
        }


        $leads = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();


        $salesUsers = User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);


        $stats = [
            'total' =>
                Lead::count(),

            'new' =>
                Lead::where(
                    'status',
                    Lead::STATUS_NEW
                )->count(),

            'followups' =>
                Lead::whereNotNull(
                    'next_followup_at'
                )
                ->whereDate(
                    'next_followup_at',
                    now()->toDateString()
                )
                ->whereNotIn(
                    'status',
                    [
                        Lead::STATUS_WON,
                        Lead::STATUS_LOST,
                    ]
                )
                ->count(),

            'won' =>
                Lead::where(
                    'status',
                    Lead::STATUS_WON
                )->count(),
        ];


        $sources = Lead::query()
            ->whereNotNull('source')
            ->distinct()
            ->orderBy('source')
            ->pluck('source');


        $businessTypes = Lead::query()
            ->whereNotNull('business_type')
            ->distinct()
            ->orderBy('business_type')
            ->pluck('business_type');


        return view(
            'admin.leads.index',
            compact(
                'leads',
                'salesUsers',
                'stats',
                'sources',
                'businessTypes'
            )
        );
    }


    public function create()
    {
        

        $salesUsers = User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'admin.leads.create',
            compact('salesUsers')
        );
    }


    public function store(
        StoreLeadRequest $request
    ) {

        $lead =
            $this->leadService->create(
                $request->validated(),
                $request->user()
            );

        return redirect()
            ->route(
                'admin.leads.show',
                $lead
            )
            ->with(
                'success',
                'Lead created successfully.'
            );
    }


    public function show(Lead $lead)
    {
        

        $lead->load([
            'assignedUser',
            'convertedCustomer',
            'activities.user',
        ]);

        $salesUsers = User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'admin.leads.show',
            compact(
                'lead',
                'salesUsers'
            )
        );
    }


    public function edit(Lead $lead)
    {
        

        $salesUsers = User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'admin.leads.edit',
            compact(
                'lead',
                'salesUsers'
            )
        );
    }


    public function update(
        UpdateLeadRequest $request,
        Lead $lead
    ) {

        $this->leadService->update(
            $lead,
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route(
                'admin.leads.show',
                $lead
            )
            ->with(
                'success',
                'Lead updated successfully.'
            );
    }


    public function destroy(
        Lead $lead
    ) {

 

        $lead->delete();

        return redirect()
            ->route(
                'admin.leads.index'
            )
            ->with(
                'success',
                'Lead moved to trash.'
            );
    }


    public function activity(
        StoreLeadActivityRequest $request,
        Lead $lead
    ) {
 

        $data =
            $request->validated();

        $this->leadService->addActivity(
            $lead,
            $request->user(),
            $data['type'],
            $data['subject'] ?? ucfirst(
                $data['type']
            ),
            $data['description'],
            $data['activity_at'],
            $data['next_followup_at'] ?? null
        );

        return back()
            ->with(
                'success',
                'Activity added successfully.'
            );
    }


    public function status(
        Request $request,
        Lead $lead
    ) {

        

        $data =
            $request->validate([
                'status' => [
                    'required',
                    'in:' . implode(
                        ',',
                        Lead::STATUSES
                    ),
                ],

                'lost_reason' => [
                    'nullable',
                    'string',
                ],
            ]);

        $this->leadService->changeStatus(
            $lead,
            $data['status'],
            $request->user(),
            $data['lost_reason'] ?? null
        );

        return back()
            ->with(
                'success',
                'Lead status updated.'
            );
    }


    public function convert(
        Request $request,
        Lead $lead
    ) {

       

        $customer =
            $this->leadService
                ->convertToCustomer(
                    $lead,
                    $request->user()
                );

        return redirect()
            ->route(
                'admin.leads.show',
                $lead
            )
            ->with(
                'success',
                "Lead converted successfully. Customer: {$customer->customer_code}"
            );
    }
}