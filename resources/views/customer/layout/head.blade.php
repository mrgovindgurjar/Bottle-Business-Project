
<header class="portal-topbar">
  <a href="{{ url('/') }}" class="portal-logo"><span>J</span> al<b>Van</b></a>
  <div class="portal-top-actions">
    <a href="{{ url('/') }}#customize" class="portal-back">← Back to website</a>
    <button class="portal-notify" aria-label="Notifications">♢<i></i></button>
    <div class="portal-user">
      <div class="portal-avatar">{{ strtoupper(substr($customer->business_name ?? $user->name, 0, 2)) }}</div>
      <div><strong>{{ $customer->business_name ?? $user->name }}</strong><small>Customer account</small></div>
      <span>⌄</span>
    </div>
  </div>
</header>