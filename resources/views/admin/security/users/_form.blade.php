@php($editing = isset($user))
<div class="security-grid">
    <div class="security-card security-section"><div class="section-title"><strong>Account details</strong><span>Login identity</span></div>
        <div class="field-grid">
            <label>Name<input name="name" value="{{ old('name',$user->name ?? '') }}" required></label>
            <label>Email<input type="email" name="email" value="{{ old('email',$user->email ?? '') }}" required></label>
            <label>Mobile<input name="mobile" value="{{ old('mobile',$user->mobile ?? '') }}" required></label>
            @if(!$editing)<label>Password<input type="password" name="password" required autocomplete="new-password"></label><label>Confirm password<input type="password" name="password_confirmation" required></label>@endif
            <label>Role<select name="role_id" required><option value="">Select role</option>@foreach($roles as $role)<option value="{{ $role->id }}" @selected(old('role_id',$user->roles->first()->id ?? '')==$role->id)>{{ $role->name }}</option>@endforeach</select></label>
            <label class="check-field"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$user->is_active ?? true))> Account active</label>
        </div>
    </div>
    <div class="security-card security-section"><div class="section-title"><strong>Access summary</strong><span>Role controls permissions</span></div>
        <p class="security-note">Users receive access through their assigned role. Do not grant permissions directly to individual users.</p>
        @if($editing)<div class="permission-preview">@foreach($user->roles->flatMap->permissions->sortBy('module') as $permission)<span>{{ $permission->slug }}</span>@endforeach</div>@else<div class="security-note">Select a role above. Permissions are inherited from that role.</div>@endif
    </div>
</div>
