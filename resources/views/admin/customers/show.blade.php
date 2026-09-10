@extends('layouts.admin')
@section('title', $customer->business_name)
@section('page_title', 'Customer Profile')
@section('breadcrumb')CRM / Customers / {{ $customer->customer_code }}@endsection
@section('content')
<div class="customer-page customer-profile-page">
    <div class="customer-profile-hero">
        <div class="customer-profile-main">
            <div class="customer-profile-avatar">{{ strtoupper(substr($customer->business_name, 0, 1)) }}</div>
            <div><div class="eyebrow">CUSTOMER ACCOUNT · {{ $customer->customer_code }}</div><h1>{{ $customer->business_name }}</h1><div class="customer-profile-sub">{{ $customer->business_type ?: 'Business customer' }} · {{ $customer->city ?: 'Location not added' }}</div><span class="customer-status status-{{ $customer->status }}"><i></i>{{ $customer->status_label }}</span></div>
        </div>
        <div class="customer-profile-actions">
            @can('update', $customer)<a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-secondary">Edit Profile</a>@endcan
            <a href="{{ route('admin.customers.index') }}" class="btn btn-primary">← Customers</a>
        </div>
    </div>

    @if(session('customer_credentials'))
        @php($credentials = session('customer_credentials'))
        <div class="customer-credentials-card">
            <div class="credentials-icon">✓</div><div><strong>Customer portal account created</strong><p>Share these credentials securely. The password is shown here only after creation.</p><div class="credentials-grid"><span><small>Login</small><b>{{ $credentials['login'] }}</b></span><span><small>Password</small><b>{{ $credentials['password'] }}</b></span></div></div>
        </div>
    @endif

    <div class="customer-profile-kpis">
        <div><small>Customer code</small><strong>{{ $customer->customer_code }}</strong></div>
        <div><small>Contact</small><strong>{{ $customer->user?->mobile ?: '—' }}</strong></div>
        <div><small>Payment terms</small><strong>{{ $customer->payment_terms_days ?? 0 }} days</strong></div>
        <div><small>Credit limit</small><strong>₹{{ number_format((float)($customer->credit_limit ?? 0), 2) }}</strong></div>
    </div>

    <div class="customer-profile-grid">
        <section class="customer-profile-card customer-profile-card-wide">
            <div class="customer-card-heading"><div><div class="eyebrow">CONTACT</div><h2>Business contact</h2></div><span class="soft-icon">◎</span></div>
            <div class="profile-detail-grid"><div><small>Contact person</small><strong>{{ $customer->user?->name ?: '—' }}</strong></div><div><small>Mobile</small><strong>{{ $customer->user?->mobile ?: '—' }}</strong></div><div><small>Email</small><strong>{{ $customer->user?->email && !str_ends_with($customer->user->email, '@jalvan.local') ? $customer->user->email : 'Not provided' }}</strong></div><div><small>GSTIN</small><strong>{{ $customer->gstin ?: 'Not provided' }}</strong></div><div><small>PAN</small><strong>{{ $customer->pan_number ?: 'Not provided' }}</strong></div><div><small>Source</small><strong>{{ $customer->source ?: 'Direct' }}</strong></div></div>
        </section>

        <section class="customer-profile-card">
            <div class="customer-card-heading"><div><div class="eyebrow">PRIMARY ADDRESS</div><h2>Business location</h2></div><span class="soft-icon">⌖</span></div>
            <div class="profile-address"><strong>{{ $customer->address ?: 'Address not added' }}</strong><span>{{ collect([$customer->city, $customer->state, $customer->pincode])->filter()->implode(', ') }}</span></div>
        </section>

        <section class="customer-profile-card customer-profile-card-wide">
            <div class="customer-card-heading"><div><div class="eyebrow">DELIVERY NETWORK</div><h2>Addresses</h2></div><span class="soft-icon">⌂</span></div>
            <div class="address-list">
                @forelse($customer->addresses as $address)
                    <div class="address-row"><div class="address-row-icon">{{ strtoupper(substr($address->type,0,1)) }}</div><div class="address-row-body"><div><strong>{{ $address->label ?: ucfirst($address->type) }}</strong>@if($address->is_default)<span class="default-pill">Default</span>@endif</div><span>{{ $address->address_line1 }}{{ $address->address_line2 ? ', '.$address->address_line2 : '' }}</span><small>{{ $address->city }}, {{ $address->state }} - {{ $address->pincode }}</small></div><div class="address-row-actions"><details class="address-edit-details"><summary title="Edit address">✎</summary><form method="POST" action="{{ route('admin.customers.addresses.update', [$customer, $address]) }}" class="address-inline-edit">@csrf @method('PUT')<div class="customer-form-grid two"><div class="customer-field"><label>Type</label><select class="customer-input" name="type"><option value="billing" @selected($address->type === 'billing')>Billing</option><option value="delivery" @selected($address->type === 'delivery')>Delivery</option><option value="office" @selected($address->type === 'office')>Office</option><option value="warehouse" @selected($address->type === 'warehouse')>Warehouse</option><option value="other" @selected($address->type === 'other')>Other</option></select></div><div class="customer-field"><label>Label</label><input class="customer-input" name="label" value="{{ $address->label }}"></div><div class="customer-field wide"><label>Address line 1</label><input class="customer-input" name="address_line1" value="{{ $address->address_line1 }}" required></div><div class="customer-field wide"><label>Address line 2</label><input class="customer-input" name="address_line2" value="{{ $address->address_line2 }}"></div><div class="customer-field"><label>City</label><input class="customer-input" name="city" value="{{ $address->city }}" required></div><div class="customer-field"><label>State</label><input class="customer-input" name="state" value="{{ $address->state }}" required></div><div class="customer-field"><label>Pincode</label><input class="customer-input" name="pincode" value="{{ $address->pincode }}" required></div><div class="customer-field"><label>Country</label><input class="customer-input" name="country" value="{{ $address->country }}" required></div><div class="customer-field"><label>Contact name</label><input class="customer-input" name="contact_name" value="{{ $address->contact_name }}"></div><div class="customer-field"><label>Contact mobile</label><input class="customer-input" name="contact_mobile" value="{{ $address->contact_mobile }}"></div></div><label class="address-checkbox"><input type="checkbox" name="is_default" value="1" @checked($address->is_default)> Make default</label><button type="submit" class="btn btn-primary">Save changes</button></form></details><form method="POST" action="{{ route('admin.customers.addresses.default', [$customer, $address]) }}">@csrf<button type="submit" title="Set default">{{ $address->is_default ? '✓' : '☆' }}</button></form><form method="POST" action="{{ route('admin.customers.addresses.destroy', [$customer, $address]) }}" data-confirm="Remove this address?">@csrf @method('DELETE')<button type="submit" title="Remove">×</button></form></div></div>
                @empty
                    <div class="profile-empty"><span>⌂</span><div><strong>No additional addresses</strong><small>Delivery addresses can be added here as soon as the delivery workflow is ready.</small></div></div>
                @endforelse
            </div>
            @can('update', $customer)
                <details class="address-add-details"><summary>＋ Add delivery address</summary><form method="POST" action="{{ route('admin.customers.addresses.store', $customer) }}" class="address-form">@csrf<div class="customer-form-grid two"><div class="customer-field"><label>Type</label><select class="customer-input" name="type"><option value="delivery">Delivery</option><option value="billing">Billing</option><option value="office">Office</option><option value="warehouse">Warehouse</option><option value="other">Other</option></select></div><div class="customer-field"><label>Label</label><input class="customer-input" name="label" placeholder="Main delivery"></div><div class="customer-field wide"><label>Address line 1</label><input class="customer-input" name="address_line1" required></div><div class="customer-field wide"><label>Address line 2</label><input class="customer-input" name="address_line2"></div><div class="customer-field"><label>City</label><input class="customer-input" name="city" required></div><div class="customer-field"><label>State</label><input class="customer-input" name="state" required></div><div class="customer-field"><label>Pincode</label><input class="customer-input" name="pincode" required></div><div class="customer-field"><label>Contact mobile</label><input class="customer-input" name="contact_mobile"></div><div class="customer-field"><label>Contact name</label><input class="customer-input" name="contact_name"></div><div class="customer-field address-checkbox"><label><input type="checkbox" name="is_default" value="1"> Make default address</label></div></div><button class="btn btn-primary" type="submit">Save Address</button></form></details>
            @endcan
        </section>

        <section class="customer-profile-card">
            <div class="customer-card-heading"><div><div class="eyebrow">COMMERCIAL</div><h2>Customer pricing</h2></div><span class="soft-icon">₹</span></div>
            @forelse($customer->prices->take(5) as $price)<div class="mini-price-row"><span>{{ $price->product?->name ?: 'Product' }}</span><strong>₹{{ number_format((float)$price->unit_price, 2) }}</strong></div>@empty<div class="profile-empty compact"><span>₹</span><div><strong>No custom pricing</strong><small>Standard pricing will apply until a customer-specific rate is configured.</small></div></div>@endforelse
        </section>
    </div>

    @if($customer->notes)
        <section class="customer-profile-card customer-notes-card"><div class="customer-card-heading"><div><div class="eyebrow">INTERNAL</div><h2>Notes</h2></div></div><p>{{ $customer->notes }}</p></section>
    @endif
</div>
@endsection
