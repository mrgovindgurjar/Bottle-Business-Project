<?php

namespace App\Services;

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
    public function create(
        array $data,
        User $user
    ): Lead {

        return DB::transaction(function () use (
            $data,
            $user
        ) {

            $data['lead_code'] =
                $this->generateLeadCode();

            $lead = Lead::create($data);

            $this->addActivity(
                $lead,
                $user,
                'note',
                'Lead created',
                'Lead was created in the system.'
            );

            if (
                !empty($data['assigned_to'])
            ) {
                $this->addActivity(
                    $lead,
                    $user,
                    'note',
                    'Lead assigned',
                    'Lead assigned to a sales executive.'
                );
            }

            return $lead;
        });
    }


    public function update(
        Lead $lead,
        array $data,
        User $user
    ): Lead {

        return DB::transaction(
            function () use (
                $lead,
                $data,
                $user
            ) {

                $oldStatus = $lead->status;

                $lead->update($data);

                if (
                    isset($data['status']) &&
                    $oldStatus !== $data['status']
                ) {

                    $this->addActivity(
                        $lead,
                        $user,
                        'note',
                        'Status changed',
                        sprintf(
                            'Lead status changed from %s to %s.',
                            ucfirst($oldStatus),
                            ucfirst($data['status'])
                        )
                    );
                }

                return $lead->fresh();
            }
        );
    }


    public function addActivity(
        Lead $lead,
        User $user,
        string $type,
        string $subject,
        string $description,
        ?string $activityAt = null,
        ?string $nextFollowupAt = null
    ): LeadActivity {

        $activity = $lead->activities()->create([
            'user_id' => $user->id,
            'type' => $type,
            'subject' => $subject,
            'description' => $description,
            'activity_at' =>
                $activityAt ?? now(),
            'next_followup_at' =>
                $nextFollowupAt,
        ]);

        if ($nextFollowupAt) {

            $lead->update([
                'next_followup_at' =>
                    $nextFollowupAt,
            ]);
        }

        if (
            in_array(
                $type,
                [
                    'call',
                    'whatsapp',
                    'email',
                    'meeting',
                ],
                true
            )
        ) {

            $lead->update([
                'last_contacted_at' => now(),
            ]);
        }

        return $activity;
    }


    public function changeStatus(
        Lead $lead,
        string $status,
        User $user,
        ?string $reason = null
    ): Lead {

        if (
            !in_array(
                $status,
                Lead::STATUSES,
                true
            )
        ) {
            throw ValidationException::withMessages([
                'status' => 'Invalid lead status.',
            ]);
        }

        if (
            $status === Lead::STATUS_LOST &&
            blank($reason)
        ) {
            throw ValidationException::withMessages([
                'lost_reason' =>
                    'Please provide a reason for losing this lead.',
            ]);
        }

        $oldStatus = $lead->status;

        $lead->update([
            'status' => $status,
            'lost_reason' =>
                $status === Lead::STATUS_LOST
                    ? $reason
                    : null,
        ]);

        $this->addActivity(
            $lead,
            $user,
            'note',
            'Lead status changed',
            sprintf(
                'Status changed from %s to %s.%s',
                ucfirst($oldStatus),
                ucfirst($status),
                $reason
                    ? " Reason: {$reason}"
                    : ''
            )
        );

        return $lead->fresh();
    }


    public function convertToCustomer(
        Lead $lead,
        User $admin
    ): Customer {

        return DB::transaction(
            function () use (
                $lead,
                $admin
            ) {

                $lead->refresh();

                if (
                    $lead->converted_customer_id
                ) {
                    throw ValidationException::withMessages([
                        'lead' =>
                            'This lead has already been converted.'
                    ]);
                }

                if (
                    $lead->status === Lead::STATUS_LOST
                ) {
                    throw ValidationException::withMessages([
                        'lead' =>
                            'A lost lead cannot be converted.'
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Existing Customer Detection
                |--------------------------------------------------------------------------
                */

                $existingCustomer = null;

                if ($lead->email) {

                    $existingCustomer =
                        Customer::whereHas(
                            'user',
                            fn ($q) =>
                                $q->where(
                                    'email',
                                    $lead->email
                                )
                        )->first();
                }

                /*
                |--------------------------------------------------------------------------
                | Create User
                |--------------------------------------------------------------------------
                */

                if ($existingCustomer) {

                    $customer =
                        $existingCustomer;

                } else {

                    $email =
                        $lead->email
                        ?: $this->generatePlaceholderEmail(
                            $lead
                        );

                    if (
                        User::where(
                            'email',
                            $email
                        )->exists()
                    ) {
                        throw ValidationException::withMessages([
                            'email' =>
                                'A user with this email already exists.'
                        ]);
                    }

                    $temporaryPassword =
                        Str::password(
                            12,
                            true,
                            true,
                            false,
                            false
                        );

                    $user = User::create([
                        'name' =>
                            $lead->contact_name
                            ?: $lead->business_name,

                        'email' =>
                            $email,

                        'mobile' =>
                            $lead->mobile,

                        'password' =>
                            Hash::make(
                                $temporaryPassword
                            ),

                        'is_active' => true,
                        
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Customer Role
                    |--------------------------------------------------------------------------
                    */

                    $customerRole =
                        Role::where(
                            'slug',
                            'customer'
                        )->first();

                    if ($customerRole) {

                        $user->roles()->syncWithoutDetaching([
                            $customerRole->id,
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Customer Code
                    |--------------------------------------------------------------------------
                    */

                    $customer =
                        Customer::create([
                            'customer_code' =>
                                $this->generateCustomerCode(),

                            'user_id' =>
                                $user->id,

                            'business_name' =>
                                $lead->business_name,

                            'address' =>
                                $lead->address,

                            'city' =>
                                $lead->city,

                            'state' =>
                                $lead->state,

                            'pincode' =>
                                $lead->pincode,

                            'source' =>
                                'lead_conversion',

                            'status' =>
                                'active',

                            'notes' =>
                                $lead->notes,
                        ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Temporary credential only for this request
                    |--------------------------------------------------------------------------
                    */

                    session()->flash(
                        'customer_credentials',
                        [
                            'email' => $email,
                            'mobile' => $lead->mobile,
                            'password' =>
                                $temporaryPassword,
                        ]
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Update Lead
                |--------------------------------------------------------------------------
                */

                $lead->update([
                    'status' =>
                        Lead::STATUS_WON,

                    'converted_customer_id' =>
                        $customer->id,
                ]);

                $this->addActivity(
                    $lead,
                    $admin,
                    'note',
                    'Lead converted',
                    sprintf(
                        'Lead converted to customer %s.',
                        $customer->customer_code
                    )
                );

                return $customer;
            }
        );
    }


    protected function generateLeadCode(): string
    {
        do {

            $code =
                'LD-'
                . now()->format('Ymd')
                . '-'
                . strtoupper(
                    Str::random(5)
                );

        } while (
            Lead::where(
                'lead_code',
                $code
            )->exists()
        );

        return $code;
    }


    protected function generateCustomerCode(): string
    {
        do {

            $code =
                'CUS-'
                . now()->format('Ymd')
                . '-'
                . strtoupper(
                    Str::random(5)
                );

        } while (
            Customer::where(
                'customer_code',
                $code
            )->exists()
        );

        return $code;
    }


    protected function generatePlaceholderEmail(
        Lead $lead
    ): string {

        return sprintf(
            'customer.%s@jalvan.local',
            strtolower(
                Str::random(12)
            )
        );
    }
}