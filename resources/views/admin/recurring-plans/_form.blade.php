@php
    $p = $p ?? null;
    $oldItems = old('items');
    $items = $oldItems !== null
        ? $oldItems
        : ($p?->items?->toArray() ?? [[]]);
    $selectedFrequency = old('frequency', $p?->frequency ?? 'daily');
    $selectedWeekdays = array_map('intval', (array) old('weekdays', $p?->weekdays ?? []));
@endphp

<div class="recurring-form-card recurring-card">
    <div class="recurring-form-grid">
        <div class="recurring-col-6">
            <label class="recurring-label">Customer <span class="recurring-required">*</span></label>
            <select name="customer_id" class="recurring-select" required>
                <option value="">Select customer</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" @selected(old('customer_id', $p?->customer_id) == $customer->id)>
                        {{ $customer->business_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="recurring-col-6">
            <label class="recurring-label">Plan Name <span class="recurring-required">*</span></label>
            <input name="name" class="recurring-input" required value="{{ old('name', $p?->name) }}" placeholder="e.g. Daily 1L Restaurant Supply">
        </div>

        <div class="recurring-col-4">
            <label class="recurring-label">Frequency <span class="recurring-required">*</span></label>
            <select name="frequency" id="frequency" class="recurring-select" required>
                @foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'] as $value => $label)
                    <option value="{{ $value }}" @selected($selectedFrequency === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="recurring-col-4">
            <label class="recurring-label">Start Date <span class="recurring-required">*</span></label>
            <input type="date" name="start_date" class="recurring-input" required value="{{ old('start_date', $p?->start_date?->format('Y-m-d') ?? today()->format('Y-m-d')) }}">
        </div>

        <div class="recurring-col-4">
            <label class="recurring-label">End Date</label>
            <input type="date" name="end_date" class="recurring-input" value="{{ old('end_date', $p?->end_date?->format('Y-m-d')) }}">
        </div>

        <div class="recurring-col-4" id="day-of-month-field" data-day-of-month>
            <label class="recurring-label">Day of Month</label>
            <input type="number" name="day_of_month" min="1" max="31" class="recurring-input" value="{{ old('day_of_month', $p?->day_of_month) }}" placeholder="1–31">
        </div>

        <div class="recurring-col-8" id="weekdays-field" data-weekdays>
            <label class="recurring-label">Weekdays</label>
            <div class="recurring-weekdays">
                @foreach([1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'] as $day => $label)
                    <label class="recurring-check">
                        <input type="checkbox" name="weekdays[]" value="{{ $day }}" @checked(in_array($day, $selectedWeekdays, true))>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="recurring-col-12">
            <label class="recurring-check">
                <input type="checkbox" name="auto_generate" value="1" @checked(old('auto_generate', $p?->auto_generate ?? true))>
                Auto generate daily orders according to this schedule
            </label>
        </div>
    </div>

    <div class="recurring-section">
        <div class="recurring-section__head">
            <div>
                <h3>Order Items</h3>
                <p>Set the recurring bottle quantity and agreed unit price.</p>
            </div>
        </div>

        <div id="plan-items">
            @foreach($items as $i => $item)
                <div class="recurring-item">
                    <div>
                        <label class="recurring-label">Product</label>
                        <select name="items[{{ $i }}][product_id]" class="recurring-select">
                            <option value="">Select product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" @selected(($item['product_id'] ?? null) == $product->id)>
                                    {{ $product->name }}{{ $product->sku ? ' · '.$product->sku : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="recurring-label">Description <span class="recurring-required">*</span></label>
                        <input name="items[{{ $i }}][description]" required class="recurring-input" value="{{ $item['description'] ?? 'Bottle' }}" placeholder="Bottle / custom product">
                    </div>

                    <div>
                        <label class="recurring-label">Quantity <span class="recurring-required">*</span></label>
                        <input name="items[{{ $i }}][quantity]" required type="number" step="0.001" min="0.001" class="recurring-input" value="{{ $item['quantity'] ?? 1 }}">
                    </div>

                    <div>
                        <label class="recurring-label">Unit Price <span class="recurring-required">*</span></label>
                        <input name="items[{{ $i }}][unit_price]" required type="number" step="0.01" min="0" class="recurring-input" value="{{ $item['unit_price'] ?? 0 }}">
                    </div>

                    <div>
                        <label class="recurring-label">Tax %</label>
                        <input name="items[{{ $i }}][tax_rate]" type="number" step="0.01" min="0" max="100" class="recurring-input" value="{{ $item['tax_rate'] ?? 0 }}">
                    </div>

                    <div>
                        <label class="recurring-label">Unit</label>
                        <input name="items[{{ $i }}][unit]" class="recurring-input" value="{{ $item['unit'] ?? 'bottle' }}" placeholder="bottle">
                    </div>

                    <input type="hidden" name="items[{{ $i }}][discount_amount]" value="{{ $item['discount_amount'] ?? 0 }}">
                    <input type="hidden" name="items[{{ $i }}][design_id]" value="{{ $item['design_id'] ?? '' }}">
                </div>
            @endforeach
        </div>
    </div>

    <div class="recurring-form-grid" style="margin-top:18px">
        <div class="recurring-col-6">
            <label class="recurring-label">Delivery Address</label>
            <textarea name="delivery_address" class="recurring-textarea" placeholder="Customer delivery address">{{ old('delivery_address', $p?->delivery_address) }}</textarea>
        </div>

        <div class="recurring-col-6">
            <label class="recurring-label">Notes</label>
            <textarea name="notes" class="recurring-textarea" placeholder="Internal notes / delivery instructions">{{ old('notes', $p?->notes) }}</textarea>
        </div>
    </div>

    <div class="recurring-form-actions">
        <a href="{{ $p ? route('admin.recurring-plans.show', $p) : route('admin.recurring-plans.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">{{ $p ? 'Update Plan' : 'Save Plan' }}</button>
    </div>
</div>
