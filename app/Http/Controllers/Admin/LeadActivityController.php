<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadActivityController extends Controller
{
    public function store(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'type' => [
                'required',
                'string',
                'max:50',
            ],

            'description' => [
                'required',
                'string',
            ],

            'activity_at' => [
                'nullable',
                'date',
            ],

            'next_followup_at' => [
                'nullable',
                'date',
            ],
        ]);

        $lead->activities()->create([
            ...$validated,
            'user_id' => auth()->id(),
            'activity_at' => $validated['activity_at'] ?? now(),
        ]);

        return back()->with(
            'success',
            'Activity added successfully.'
        );
    }
}