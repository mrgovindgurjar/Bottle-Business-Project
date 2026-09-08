@csrf

<div class="lead-form-grid">

    <div class="form-section">

        <div class="form-section-header">
            <h3>Business Information</h3>
            <span>Basic information about the prospect.</span>
        </div>


        <div class="form-grid-2">

            <div class="form-group">

                <label class="form-label">
                    Business Name *
                </label>

                <input
                    type="text"
                    name="business_name"
                    class="form-control"
                    value="{{ old(
                        'business_name',
                        $lead->business_name ?? ''
                    ) }}"
                    placeholder="e.g. Taste of India"
                    required
                >

                @error('business_name')
                    <div class="field-error">
                        {{ $message }}
                    </div>
                @enderror

            </div>


            <div class="form-group">

                <label class="form-label">
                    Business Type
                </label>

                <select
                    name="business_type"
                    class="form-control"
                >

                    <option value="">
                        Select type
                    </option>

                    @foreach([
                        'Restaurant',
                        'Hotel',
                        'Cafe',
                        'Corporate',
                        'Event',
                        'Caterer',
                        'Retail',
                        'Other',
                    ] as $type)

                        <option
                            value="{{ $type }}"
                            @selected(
                                old(
                                    'business_type',
                                    $lead->business_type ?? ''
                                ) === $type
                            )
                        >
                            {{ $type }}
                        </option>

                    @endforeach

                </select>

            </div>

        </div>

    </div>


    <div class="form-section">

        <div class="form-section-header">
            <h3>Contact Information</h3>
            <span>Who should the sales team contact?</span>
        </div>


        <div class="form-grid-2">

            <div class="form-group">

                <label class="form-label">
                    Contact Person
                </label>

                <input
                    type="text"
                    name="contact_name"
                    class="form-control"
                    value="{{ old(
                        'contact_name',
                        $lead->contact_name ?? ''
                    ) }}"
                    placeholder="Contact person's name"
                >

            </div>


            <div class="form-group">

                <label class="form-label">
                    Mobile
                </label>

                <input
                    type="text"
                    name="mobile"
                    class="form-control"
                    value="{{ old(
                        'mobile',
                        $lead->mobile ?? ''
                    ) }}"
                    placeholder="Mobile number"
                >

            </div>


            <div class="form-group">

                <label class="form-label">
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    value="{{ old(
                        'email',
                        $lead->email ?? ''
                    ) }}"
                    placeholder="business@example.com"
                >

            </div>


            <div class="form-group">

                <label class="form-label">
                    Lead Source
                </label>

                <select
                    name="source"
                    class="form-control"
                >

                    <option value="">
                        Select source
                    </option>

                    @foreach([
                        'Website',
                        'WhatsApp',
                        'Phone',
                        'Referral',
                        'Instagram',
                        'Facebook',
                        'Google',
                        'Cold Outreach',
                        'Existing Customer',
                        'Other',
                    ] as $source)

                        <option
                            value="{{ $source }}"
                            @selected(
                                old(
                                    'source',
                                    $lead->source ?? ''
                                ) === $source
                            )
                        >
                            {{ $source }}
                        </option>

                    @endforeach

                </select>

            </div>

        </div>

    </div>


    <div class="form-section">

        <div class="form-section-header">
            <h3>Requirement</h3>
            <span>Capture the business opportunity.</span>
        </div>


        <div class="form-grid-3">

            <div class="form-group">

                <label class="form-label">
                    Estimated Quantity
                </label>

                <input
                    type="number"
                    name="estimated_quantity"
                    min="1"
                    class="form-control"
                    value="{{ old(
                        'estimated_quantity',
                        $lead->estimated_quantity ?? ''
                    ) }}"
                    placeholder="200"
                >

            </div>


            <div class="form-group">

                <label class="form-label">
                    Quantity Unit
                </label>

                <select
                    name="quantity_unit"
                    class="form-control"
                >

                    @foreach([
                        'bottle',
                        'box',
                        'unit',
                    ] as $unit)

                        <option
                            value="{{ $unit }}"
                            @selected(
                                old(
                                    'quantity_unit',
                                    $lead->quantity_unit ?? 'bottle'
                                ) === $unit
                            )
                        >
                            {{ ucfirst($unit) }}
                        </option>

                    @endforeach

                </select>

            </div>


            <div class="form-group">

                <label class="form-label">
                    Order Frequency
                </label>

                <select
                    name="order_frequency"
                    class="form-control"
                >

                    <option value="">
                        Select frequency
                    </option>

                    @foreach([
                        'One Time',
                        'Daily',
                        'Weekly',
                        'Monthly',
                        'Event',
                        'Recurring',
                    ] as $frequency)

                        <option
                            value="{{ $frequency }}"
                            @selected(
                                old(
                                    'order_frequency',
                                    $lead->order_frequency ?? ''
                                ) === $frequency
                            )
                        >
                            {{ $frequency }}
                        </option>

                    @endforeach

                </select>

            </div>

        </div>


        <div class="form-grid-2">

            <div class="form-group">

                <label class="form-label">
                    Estimated Value
                </label>

                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="estimated_value"
                    class="form-control"
                    value="{{ old(
                        'estimated_value',
                        $lead->estimated_value ?? ''
                    ) }}"
                    placeholder="₹ 50,000"
                >

            </div>


            <div class="form-group">

                <label class="form-label">
                    Requirement Details
                </label>

                <textarea
                    name="requirement"
                    class="form-control"
                    rows="4"
                    placeholder="750ml custom branded bottles, QR code, delivery requirement..."
                >{{ old(
                    'requirement',
                    $lead->requirement ?? ''
                ) }}</textarea>

            </div>

        </div>

    </div>


    <div class="form-section">

        <div class="form-section-header">
            <h3>Assignment & Follow-up</h3>
            <span>Keep every opportunity accountable.</span>
        </div>


        <div class="form-grid-3">

            <div class="form-group">

                <label class="form-label">
                    Assigned To
                </label>

                <select
                    name="assigned_to"
                    class="form-control"
                >

                    <option value="">
                        Unassigned
                    </option>

                    @foreach($salesUsers as $salesUser)

                        <option
                            value="{{ $salesUser->id }}"
                            @selected(
                                (string) old(
                                    'assigned_to',
                                    $lead->assigned_to ?? ''
                                )
                                ===
                                (string) $salesUser->id
                            )
                        >
                            {{ $salesUser->name }}
                        </option>

                    @endforeach

                </select>

            </div>


            <div class="form-group">

                <label class="form-label">
                    Status
                </label>

                <select
                    name="status"
                    class="form-control"
                >

                    @foreach(\App\Models\Lead::STATUSES as $status)

                        <option
                            value="{{ $status }}"
                            @selected(
                                old(
                                    'status',
                                    $lead->status ?? 'new'
                                ) === $status
                            )
                        >
                            {{ ucfirst($status) }}
                        </option>

                    @endforeach

                </select>

            </div>


            <div class="form-group">

                <label class="form-label">
                    Next Follow-up
                </label>

                <input
                    type="datetime-local"
                    name="next_followup_at"
                    class="form-control"
                    value="{{ old(
                        'next_followup_at',
                        isset($lead) && $lead->next_followup_at
                            ? $lead->next_followup_at
                                ->format('Y-m-d\TH:i')
                            : ''
                    ) }}"
                >

            </div>

        </div>

    </div>


    <div class="form-section">

        <div class="form-section-header">
            <h3>Address & Notes</h3>
        </div>


        <div class="form-group">

            <label class="form-label">
                Address
            </label>

            <textarea
                name="address"
                class="form-control"
                rows="2"
            >{{ old(
                'address',
                $lead->address ?? ''
            ) }}</textarea>

        </div>


        <div class="form-grid-3">

            <div class="form-group">

                <label class="form-label">
                    City
                </label>

                <input
                    type="text"
                    name="city"
                    class="form-control"
                    value="{{ old(
                        'city',
                        $lead->city ?? ''
                    ) }}"
                >

            </div>


            <div class="form-group">

                <label class="form-label">
                    State
                </label>

                <input
                    type="text"
                    name="state"
                    class="form-control"
                    value="{{ old(
                        'state',
                        $lead->state ?? ''
                    ) }}"
                >

            </div>


            <div class="form-group">

                <label class="form-label">
                    Pincode
                </label>

                <input
                    type="text"
                    name="pincode"
                    class="form-control"
                    value="{{ old(
                        'pincode',
                        $lead->pincode ?? ''
                    ) }}"
                >

            </div>

        </div>


        <div class="form-group">

            <label class="form-label">
                Internal Notes
            </label>

            <textarea
                name="notes"
                class="form-control"
                rows="3"
                placeholder="Anything the sales team should know..."
            >{{ old(
                'notes',
                $lead->notes ?? ''
            ) }}</textarea>

        </div>

    </div>

</div>


<div class="form-actions">

    <a
        href="{{ route('admin.leads.index') }}"
        class="btn btn-secondary"
    >
        Cancel
    </a>

    <button
        type="submit"
        class="btn btn-primary"
    >
        {{ isset($lead) ? 'Save Changes' : 'Create Lead' }}
    </button>

</div>