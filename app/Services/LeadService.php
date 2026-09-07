<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Support\Facades\DB;

class LeadService
{
    public function create(array $data): Lead
    {
        return DB::transaction(function () use ($data) {

            $data['lead_code'] = $this->generateLeadCode();

            $lead = Lead::create($data);

            $lead->activities()->create([
                'user_id' => auth()->id(),
                'type' => 'note',
                'description' => 'Lead created',
                'activity_at' => now(),
            ]);

            return $lead;
        });
    }

    public function update(Lead $lead, array $data): Lead
    {
        $lead->update($data);

        return $lead->fresh();
    }

    private function generateLeadCode(): string
    {
        $lastId = Lead::max('id') ?? 0;

        return 'LD-' . str_pad(
            $lastId + 1,
            5,
            '0',
            STR_PAD_LEFT
        );
    }
}