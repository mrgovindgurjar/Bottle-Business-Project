<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Lead;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function __construct(
        protected LeadService $leadService
    ) {
    }

    public function index(): View
    {
        $leads = Lead::with('assignedUser')
            ->latest()
            ->paginate(20);

        return view('admin.leads.index', compact('leads'));
    }

    public function create(): View
    {
        $users = User::where('is_active', true)->get();

        return view('admin.leads.create', compact('users'));
    }

    public function store(
        StoreLeadRequest $request
    ): RedirectResponse {

        $lead = $this->leadService->create(
            $request->validated()
        );

        return redirect()
            ->route('admin.leads.show', $lead)
            ->with('success', 'Lead created successfully.');
    }

    public function show(Lead $lead): View
    {
        $lead->load([
            'assignedUser',
            'activities.user',
        ]);

        return view('admin.leads.show', compact('lead'));
    }

    public function edit(Lead $lead): View
    {
        $users = User::where('is_active', true)->get();

        return view(
            'admin.leads.edit',
            compact('lead', 'users')
        );
    }

    public function update(
        UpdateLeadRequest $request,
        Lead $lead
    ): RedirectResponse {

        $this->leadService->update(
            $lead,
            $request->validated()
        );

        return redirect()
            ->route('admin.leads.show', $lead)
            ->with('success', 'Lead updated successfully.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $lead->delete();

        return redirect()
            ->route('admin.leads.index')
            ->with('success', 'Lead deleted successfully.');
    }
}