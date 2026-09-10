@csrf
@if(isset($customer))
    @method('PUT')
@endif

<div class="customer-form-shell">
    <div class="customer-form-section">
        <div class="customer-form-section-head">
            <div class="customer-section-number">01</div>
            <div><h2>Business information</h2><p>The business identity used across quotations, orders and invoices.</p></div>
        </div>
        <div class="customer-form-grid two">
            <div class="customer-field wide">
                <label for="business_name">Business name <em>*</em></label>
                <input id="business_name" class="customer-input" name="business_name" value="{{ old('business_name', $customer->business_name ?? '') }}" placeholder="e.g. Taste of India" required>
                @error('business_name')<span class="customer-error">{{ $message }}</span>@enderror
            </div>
            <div class="customer-field">
                <label for="business_type">Business type</label>
                <select id="business_type" class="customer-input" name="business_type">
                    <option value="">Select type</option>
                    @foreach(['Restaurant','Hotel','Cafe','Event / Catering','Corporate','Retail','Other'] as $type)
                        <option value="{{ $type }}" @selected(old('business_type', $customer->business_type ?? '') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="customer-field">
                <label for="source">Customer source</label>
                <select id="source" class="customer-input" name="source">
                    <option value="">Select source</option>
                    @foreach(['Website','Lead','WhatsApp','Referral','Call','Walk-in','Other'] as $source)
                        <option value="{{ $source }}" @selected(old('source', $customer->source ?? '') === $source)>{{ $source }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="customer-form-section">
        <div class="customer-form-section-head">
            <div class="customer-section-number">02</div>
            <div><h2>Primary contact</h2><p>Login and communication details for the business account.</p></div>
        </div>
        <div class="customer-form-grid three">
            <div class="customer-field">
                <label for="contact_name">Contact person <em>*</em></label>
                <input id="contact_name" class="customer-input" name="contact_name" value="{{ old('contact_name', $customer->user->name ?? '') }}" placeholder="Full name" required>
                @error('contact_name')<span class="customer-error">{{ $message }}</span>@enderror
            </div>
            <div class="customer-field">
                <label for="mobile">Mobile <em>*</em></label>
                <input id="mobile" class="customer-input" name="mobile" value="{{ old('mobile', $customer->user->mobile ?? '') }}" placeholder="10 digit mobile" inputmode="tel" required>
                @error('mobile')<span class="customer-error">{{ $message }}</span>@enderror
            </div>
            <div class="customer-field">
                <label for="email">Email</label>
                <input id="email" class="customer-input" type="email" name="email" value="{{ old('email', isset($customer) && str_ends_with($customer->user->email ?? '', '@jalvan.local') ? '' : ($customer->user->email ?? '')) }}" placeholder="business@example.com">
                @error('email')<span class="customer-error">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>

    <div class="customer-form-section">
        <div class="customer-form-section-head">
            <div class="customer-section-number">03</div>
            <div><h2>Tax & commercial profile</h2><p>Optional details used later by pricing, billing and finance modules.</p></div>
        </div>
        <div class="customer-form-grid four">
            <div class="customer-field"><label for="gstin">GSTIN</label><input id="gstin" class="customer-input" name="gstin" value="{{ old('gstin', $customer->gstin ?? '') }}" placeholder="15-character GSTIN"></div>
            <div class="customer-field"><label for="pan_number">PAN</label><input id="pan_number" class="customer-input" name="pan_number" value="{{ old('pan_number', $customer->pan_number ?? '') }}" placeholder="PAN number"></div>
            <div class="customer-field"><label for="payment_terms_days">Payment terms</label><div class="customer-input-suffix"><input id="payment_terms_days" class="customer-input" type="number" min="0" max="365" name="payment_terms_days" value="{{ old('payment_terms_days', $customer->payment_terms_days ?? 0) }}"><span>days</span></div></div>
            <div class="customer-field"><label for="credit_limit">Credit limit</label><div class="customer-input-prefix"><span>₹</span><input id="credit_limit" class="customer-input" type="number" step="0.01" min="0" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit ?? 0) }}"></div></div>
        </div>
    </div>

    <div class="customer-form-section">
        <div class="customer-form-section-head">
            <div class="customer-section-number">04</div>
            <div><h2>Address</h2><p>Primary business address. Multiple delivery addresses can be managed from the customer profile.</p></div>
        </div>
        <div class="customer-form-grid two">
            <div class="customer-field wide"><label for="address">Address</label><textarea id="address" class="customer-input" name="address" rows="3" placeholder="Street, building, area...">{{ old('address', $customer->address ?? '') }}</textarea></div>
            <div class="customer-field"><label for="city">City</label><input id="city" class="customer-input" name="city" value="{{ old('city', $customer->city ?? '') }}" placeholder="Bhopal"></div>
            <div class="customer-field"><label for="state">State</label><input id="state" class="customer-input" name="state" value="{{ old('state', $customer->state ?? '') }}" placeholder="Madhya Pradesh"></div>
            <div class="customer-field"><label for="pincode">Pincode</label><input id="pincode" class="customer-input" name="pincode" value="{{ old('pincode', $customer->pincode ?? '') }}" placeholder="462001"></div>
            <div class="customer-field"><label for="currency">Currency</label><input id="currency" class="customer-input" name="currency" value="{{ old('currency', $customer->currency ?? 'INR') }}" maxlength="3"></div>
        </div>
    </div>

    @if(isset($customer))
        <div class="customer-form-section">
            <div class="customer-form-section-head"><div class="customer-section-number">05</div><div><h2>Account status</h2><p>Control whether this customer account can continue using the system.</p></div></div>
            <div class="customer-form-grid two">
                <div class="customer-field"><label for="status">Status</label><select id="status" class="customer-input" name="status"><option value="active" @selected(old('status', $customer->status) === 'active')>Active</option><option value="inactive" @selected(old('status', $customer->status) === 'inactive')>Inactive</option><option value="blocked" @selected(old('status', $customer->status) === 'blocked')>Blocked</option></select></div>
                <div class="customer-field"><label for="password">Reset password <span>optional</span></label><input id="password" class="customer-input" type="password" name="password" placeholder="Leave blank to keep current password"></div>
            </div>
        </div>
    @else
        <div class="customer-form-section">
            <div class="customer-form-section-head"><div class="customer-section-number">05</div><div><h2>Customer portal access</h2><p>A temporary password is generated automatically if you leave this blank.</p></div></div>
            <div class="customer-form-grid two">
                <div class="customer-field"><label for="password">Temporary password <span>optional</span></label><input id="password" class="customer-input" type="password" name="password" placeholder="Minimum 8 characters"></div>
                <div class="customer-login-note"><span>↗</span><div><strong>Portal account included</strong><small>The customer will be created with the Customer role and can log in using their mobile number.</small></div></div>
            </div>
        </div>
    @endif

    <div class="customer-form-section compact">
        <div class="customer-form-section-head"><div class="customer-section-number">06</div><div><h2>Internal notes</h2><p>Private notes for your JALVAN team.</p></div></div>
        <textarea class="customer-input" name="notes" rows="4" placeholder="Add internal notes, preferences or commercial context...">{{ old('notes', $customer->notes ?? '') }}</textarea>
    </div>

    <div class="customer-form-actions">
        <a href="{{ isset($customer) ? route('admin.customers.show', $customer) : route('admin.customers.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary customer-save-btn"><span>✓</span> {{ isset($customer) ? 'Save Customer' : 'Create Customer' }}</button>
    </div>
</div>
