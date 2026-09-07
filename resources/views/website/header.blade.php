<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#f5fbff">
  <title>JalVan — Custom Branded Water Bottles</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://cdn.jsdelivr.net">
  <script src="https://cdn.jsdelivr.net/npm/lenis@1.1.20/dist/lenis.min.js"></script>
  <link rel="stylesheet" href="{{ asset('website/styles.css') }}">
</head>
<body>
<div class="page-loader" id="pageLoader">
  <div class="loader-brand"><span class="loader-mark">J</span><b>an<span>Van</span></b></div>
  <div class="loader-progress"><i></i></div>
  <small>BUILDING YOUR BOTTLE EXPERIENCE</small>
</div>
<div class="scroll-line" id="scrollLine"></div>

<nav class="navbar navbar-expand-lg fixed-top site-nav">
  <div class="container">
    <a class="navbar-brand brand" href="#home">
        <!-- <span class="brand-icon">J</span>al<span>Van</span> -->
         <img src="{{ asset('website/img/logo.png') }}" alt="" height="80px" width="160px">
    </a>
    <button class="navbar-toggler border-0 shadow-none" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
        <li><a class="nav-link" href="#solutions">Solutions</a></li>
        <li><a class="nav-link" href="#showcase">Our work</a></li>
        <li><a class="nav-link" href="#process">How it works</a></li>
        <li><a class="nav-link" href="#customize">Customize</a></li>
        <li><a class="nav-link" href="{{ route('customer.main') }}" href="customer-portal.html">Customer login</a></li>
        <li><a class="btn btn-dark rounded-pill px-4 ms-lg-2" href="#customize">Start a design ↗</a></li>
      </ul>
    </div>
  </div>
</nav>

