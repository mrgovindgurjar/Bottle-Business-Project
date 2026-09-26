
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
   <title>@yield('title')</title>
  <meta name="description" content="AquaForm customer portal for custom branded water bottle orders, designs and production tracking.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('website/styles.css') }}">
  <link rel="stylesheet" href="{{ asset('Customer/portal.css') }}">
</head>
<body class="portal-page">

<div class="portal-loader" id="portalLoader">
  <div class="portal-loader-mark">J</div>
  <strong>Jal<span>Van</span></strong>
</div>

@include('customer.layout.head')


<div class="portal-shell">
  <aside class="portal-sidebar">
    <div class="portal-sidebar-label">CUSTOMER PORTAL</div>
    <nav>
      <button class="portal-nav active" data-view="overview"><span>⌂</span> Overview</button>
      <button class="portal-nav" data-view="orders"><span>▣</span> My orders <b>2</b></button>
      <button class="portal-nav" data-view="designs"><span>◇</span> Designs <b class="blue">1</b></button>
      <button class="portal-nav" data-view="documents"><span>□</span> Documents</button>
      <button class="portal-nav" data-view="profile"><span>○</span> Business profile</button>
    </nav>
    <div class="portal-help">
      <span>?</span>
      <strong>Need help?</strong>
      <p>Your AquaForm team is here for design, quote and order support.</p>
      <button>Talk to team ↗</button>
    </div>
    <form method="POST" action="{{ route('logout') }}" class="portal-logout-form">@csrf<button type="submit" class="portal-logout">↪ Sign out</button></form>
  </aside>

 @yield('content')

 @include('customer.layout.footer')