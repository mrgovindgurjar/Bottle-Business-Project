<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerService
{
    public function create(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $plainPassword = $data['password'] ?? Str::random(12);
            $email = $data['email'] ?? $this->placeholderEmail();

            $user = User::create([
                'name' => $data['contact_name'],
                'email' => $email,
                'mobile' => $data['mobile'],
                'password' => Hash::make($plainPassword),
                'is_active' => true,
            ]);

            $customer = Customer::create([
                'customer_code' => $this->generateCustomerCode(),
                'user_id' => $user->id,
                'business_name' => $data['business_name'],
                'business_type' => $data['business_type'] ?? null,
                'gstin' => $data['gstin'] ?? null,
                'pan_number' => $data['pan_number'] ?? null,
                'payment_terms_days' => $data['payment_terms_days'] ?? 0,
                'credit_limit' => $data['credit_limit'] ?? 0,
                'currency' => strtoupper($data['currency'] ?? 'INR'),
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'pincode' => $data['pincode'] ?? null,
                'source' => $data['source'] ?? null,
                'status' => $data['status'] ?? Customer::STATUS_ACTIVE,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->assignCustomerRole($user);
            AuditLog::record('customer.created', $customer, [], ['business_name'=>$customer->business_name,'customer_code'=>$customer->customer_code,'user_id'=>$user->id]);

            return [
                'customer' => $customer,
                'credentials' => [
                    'login' => $data['mobile'],
                    'email' => $email,
                    'password' => $plainPassword,
                    'generated_password' => empty($data['password']),
                ],
            ];
        });
    }

    public function update(Customer $customer, array $data): Customer
    {
        return DB::transaction(function () use ($customer, $data) {
            $user = $customer->user;

            $user->update([
                'name' => $data['contact_name'],
                'email' => $data['email'] ?? $user->email,
                'mobile' => $data['mobile'],
                'is_active' => ($data['status'] ?? $customer->status) !== Customer::STATUS_BLOCKED,
            ]);

            if (!empty($data['password'])) {
                $user->update([
                    'password' => Hash::make($data['password']),
                ]);
            }

            $customer->update([
                'business_name' => $data['business_name'],
                'business_type' => $data['business_type'] ?? null,
                'gstin' => $data['gstin'] ?? null,
                'pan_number' => $data['pan_number'] ?? null,
                'payment_terms_days' => $data['payment_terms_days'] ?? 0,
                'credit_limit' => $data['credit_limit'] ?? 0,
                'currency' => strtoupper($data['currency'] ?? 'INR'),
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'pincode' => $data['pincode'] ?? null,
                'source' => $data['source'] ?? null,
                'status' => $data['status'] ?? $customer->status,
                'notes' => $data['notes'] ?? null,
            ]);

            $fresh = $customer->fresh(['user']);
            AuditLog::record('customer.updated', $fresh, [], ['business_name'=>$fresh->business_name,'status'=>$fresh->status]);
            return $fresh;
        });
    }

    public function delete(Customer $customer): void
    {
        DB::transaction(function () use ($customer) {
            $customer->update([
                'status' => Customer::STATUS_INACTIVE,
            ]);

            $customer->user?->update([
                'is_active' => false,
            ]);

            $customer->delete();
            AuditLog::record('customer.deactivated', $customer, ['status'=>Customer::STATUS_ACTIVE], ['status'=>Customer::STATUS_INACTIVE]);
        });
    }

    public function saveAddress(
        Customer $customer,
        array $data,
        ?CustomerAddress $address = null
    ): CustomerAddress {
        return DB::transaction(function () use ($customer, $data, $address) {
            $isDefault = (bool) ($data['is_default'] ?? false);

            if ($isDefault) {
                $customer->addresses()->update([
                    'is_default' => false,
                ]);
            }

            if ($address) {
                $address->update($data);
                return $address->fresh();
            }

            return $customer->addresses()->create($data);
        });
    }

    public function deleteAddress(CustomerAddress $address): void
    {
        $customer = $address->customer;
        $wasDefault = $address->is_default;

        DB::transaction(function () use ($address) {
            $address->delete();
        });

        if ($wasDefault) {
            $customer->addresses()
                ->latest('id')
                ->first()
                ?->update(['is_default' => true]);
        }
    }

    protected function assignCustomerRole(User $user): void
    {
        $role = DB::table('roles')
            ->where('slug', 'customer')
            ->first();

        if ($role) {
            DB::table('user_roles')->updateOrInsert(
                [
                    'user_id' => $user->id,
                    'role_id' => $role->id,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    protected function generateCustomerCode(): string
    {
        do {
            $code = 'CUS-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
        } while (Customer::where('customer_code', $code)->exists());

        return $code;
    }

    protected function placeholderEmail(): string
    {
        do {
            $email = 'customer.' . strtolower(Str::random(16)) . '@jalvan.local';
        } while (User::where('email', $email)->exists());

        return $email;
    }
}
