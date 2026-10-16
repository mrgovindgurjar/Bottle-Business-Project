<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LeadService
{
    public function create(array $data, User $user): Lead
    {
        return DB::transaction(function () use ($data, $user) {
            $data['lead_code'] = $this->generateLeadCode();
            $lead = Lead::create($data);

            $this->addActivity($lead, $user, 'note', 'Lead created', 'Lead was created in the system.');
            if (!empty($data['assigned_to'])) {
                $this->addActivity($lead, $user, 'note', 'Lead assigned', 'Lead assigned to a sales executive.');
            }

            AuditLog::record('lead.created', $lead, [], [
                'lead_code' => $lead->lead_code,
                'business_name' => $lead->business_name,
                'assigned_to' => $lead->assigned_to,
                'status' => $lead->status,
            ], 'Lead created.');

            return $lead;
        });
    }

    public function update(Lead $lead, array $data, User $user): Lead
    {
        return DB::transaction(function () use ($lead, $data, $user) {
            $before = $lead->only(['business_name', 'contact_name', 'mobile', 'email', 'assigned_to', 'status', 'next_followup_at', 'estimated_quantity', 'estimated_value']);
            $oldStatus = $lead->status;
            $oldAssignee = $lead->assigned_to;

            $lead->update($data);

            if (isset($data['status']) && $oldStatus !== $data['status']) {
                $this->addActivity($lead, $user, 'note', 'Status changed', sprintf('Lead status changed from %s to %s.', ucfirst($oldStatus), ucfirst($data['status'])));
            }
            if (array_key_exists('assigned_to', $data) && $oldAssignee != $data['assigned_to']) {
                $this->addActivity($lead, $user, 'note', 'Lead reassigned', $data['assigned_to'] ? 'Lead assigned to a sales executive.' : 'Lead assignment removed.');
            }

            AuditLog::record('lead.updated', $lead, $before, $lead->only(['business_name', 'contact_name', 'mobile', 'email', 'assigned_to', 'status', 'next_followup_at', 'estimated_quantity', 'estimated_value']), 'Lead updated.');
            return $lead->fresh();
        });
    }

    public function delete(Lead $lead, User $user): void
    {
        DB::transaction(function () use ($lead) {
            $lead->delete();
        });
        AuditLog::record('lead.deleted', $lead, ['lead_code' => $lead->lead_code], [], 'Lead moved to trash.');
    }

    public function addActivity(Lead $lead, User $user, string $type, string $subject, string $description, ?string $activityAt = null, ?string $nextFollowupAt = null): LeadActivity
    {
        $activity = $lead->activities()->create([
            'user_id' => $user->id,
            'type' => $type,
            'subject' => $subject,
            'description' => $description,
            'activity_at' => $activityAt ?? now(),
            'next_followup_at' => $nextFollowupAt,
        ]);

        if ($nextFollowupAt) {
            $lead->update(['next_followup_at' => $nextFollowupAt]);
        }

        if (in_array($type, ['call', 'whatsapp', 'email', 'meeting'], true)) {
            $lead->update(['last_contacted_at' => now()]);
        }

        AuditLog::record('lead.activity_added', $lead, [], [
            'activity_id' => $activity->id,
            'type' => $type,
            'subject' => $subject,
            'next_followup_at' => $nextFollowupAt,
        ], 'Lead activity added.');

        return $activity;
    }

    public function changeStatus(Lead $lead, string $status, User $user, ?string $reason = null): Lead
    {
        if (!in_array($status, Lead::STATUSES, true)) {
            throw ValidationException::withMessages(['status' => 'Invalid lead status.']);
        }
        if ($status === Lead::STATUS_LOST && blank($reason)) {
            throw ValidationException::withMessages(['lost_reason' => 'Please provide a reason for losing this lead.']);
        }

        $oldStatus = $lead->status;
        $lead->update([
            'status' => $status,
            'lost_reason' => $status === Lead::STATUS_LOST ? $reason : null,
        ]);

        $this->addActivity($lead, $user, 'note', 'Lead status changed', sprintf(
            'Status changed from %s to %s.%s', ucfirst($oldStatus), ucfirst($status), $reason ? " Reason: {$reason}" : ''
        ));

        AuditLog::record('lead.status_changed', $lead, ['status' => $oldStatus], ['status' => $status, 'lost_reason' => $reason], 'Lead status changed.');
        return $lead->fresh();
    }

    public function convertToCustomer(Lead $lead, User $admin): Customer
    {
        return DB::transaction(function () use ($lead, $admin) {
            $lead->refresh();
            if ($lead->converted_customer_id) {
                throw ValidationException::withMessages(['lead' => 'This lead has already been converted.']);
            }
            if ($lead->status === Lead::STATUS_LOST) {
                throw ValidationException::withMessages(['lead' => 'A lost lead cannot be converted.']);
            }

            $existingCustomer = Customer::query()
                ->whereHas('user', function ($q) use ($lead) {
                    $q->where(function ($inner) use ($lead) {
                        if ($lead->email) $inner->orWhere('email', $lead->email);
                        if ($lead->mobile) $inner->orWhere('mobile', $lead->mobile);
                    });
                })
                ->first();

            if ($existingCustomer) {
                $customer = $existingCustomer;
            } else {
                $email = $lead->email ?: $this->generatePlaceholderEmail();

                $existingUser = User::query()->where(function ($q) use ($email, $lead) {
                    $q->where('email', $email);
                    if ($lead->mobile) $q->orWhere('mobile', $lead->mobile);
                })->first();

                if ($existingUser) {
                    throw ValidationException::withMessages([
                        'lead' => 'A user already exists with this email or mobile. Link that account to a customer before converting this lead.',
                    ]);
                }

                $temporaryPassword = Str::password(12, true, true, false, false);
                $user = User::create([
                    'name' => $lead->contact_name ?: $lead->business_name,
                    'email' => $email,
                    'mobile' => $lead->mobile,
                    'password' => Hash::make($temporaryPassword),
                    'is_active' => true,
                ]);

                $customerRole = Role::where('slug', 'customer')->first();
                if ($customerRole) {
                    $user->roles()->syncWithoutDetaching([$customerRole->id]);
                }

                $customer = Customer::create([
                    'customer_code' => $this->generateCustomerCode(),
                    'user_id' => $user->id,
                    'business_name' => $lead->business_name,
                    'address' => $lead->address,
                    'city' => $lead->city,
                    'state' => $lead->state,
                    'pincode' => $lead->pincode,
                    'source' => 'lead_conversion',
                    'status' => Customer::STATUS_ACTIVE,
                    'notes' => $lead->notes,
                ]);

                session()->flash('customer_credentials', [
                    'email' => $email,
                    'mobile' => $lead->mobile,
                    'password' => $temporaryPassword,
                ]);
            }

            $lead->update([
                'status' => Lead::STATUS_WON,
                'converted_customer_id' => $customer->id,
            ]);

            $this->addActivity($lead, $admin, 'note', 'Lead converted', sprintf('Lead converted to customer %s.', $customer->customer_code));
            AuditLog::record('lead.converted', $lead, [], [
                'customer_id' => $customer->id,
                'customer_code' => $customer->customer_code,
            ], 'Lead converted to customer.');

            return $customer;
        });
    }

    protected function generateLeadCode(): string
    {
        do {
            $code = 'LD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        } while (Lead::withTrashed()->where('lead_code', $code)->exists());
        return $code;
    }

    protected function generateCustomerCode(): string
    {
        do {
            $code = 'CUS-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        } while (Customer::withTrashed()->where('customer_code', $code)->exists());
        return $code;
    }

    protected function generatePlaceholderEmail(): string
    {
        do {
            $email = 'customer.' . strtolower(Str::random(16)) . '@jalvan.local';
        } while (User::where('email', $email)->exists());
        return $email;
    }
}
