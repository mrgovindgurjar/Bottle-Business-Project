<div class="sp-form-grid">
  <label>Business Name *<input name="business_name" value="{{ old('business_name',$supplier->business_name ?? '') }}" required></label>
  <label>Contact Person<input name="contact_name" value="{{ old('contact_name',$supplier->contact_name ?? '') }}"></label>
  <label>Mobile<input name="mobile" value="{{ old('mobile',$supplier->mobile ?? '') }}"></label>
  <label>Email<input type="email" name="email" value="{{ old('email',$supplier->email ?? '') }}"></label>
  <label>GSTIN<input name="gstin" value="{{ old('gstin',$supplier->gstin ?? '') }}"></label>
  <label>Category<input name="category" value="{{ old('category',$supplier->category ?? '') }}" placeholder="Bottle, Cap, Label, Water..."></label>
  <label>Payment Terms (days)<input type="number" min="0" name="payment_terms_days" value="{{ old('payment_terms_days',$supplier->payment_terms_days ?? 0) }}"></label>
  <label>Status<select name="status">@foreach(['active','inactive','blocked'] as $s)<option value="{{ $s }}" @selected(old('status',$supplier->status ?? 'active')===$s)>{{ ucfirst($s) }}</option>@endforeach</select></label>
  <label class="sp-span-2">Address<input name="address" value="{{ old('address',$supplier->address ?? '') }}"></label>
  <label>City<input name="city" value="{{ old('city',$supplier->city ?? '') }}"></label><label>State<input name="state" value="{{ old('state',$supplier->state ?? '') }}"></label><label>Pincode<input name="pincode" value="{{ old('pincode',$supplier->pincode ?? '') }}"></label>
  <label class="sp-span-2">Notes<textarea name="notes" rows="4">{{ old('notes',$supplier->notes ?? '') }}</textarea></label>
</div>
@if($errors->any())<div class="sp-errors">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
<div class="sp-form-actions"><a class="sp-btn" href="{{ route('admin.suppliers.index') }}">Cancel</a><button class="sp-btn sp-primary">Save Supplier</button></div>
