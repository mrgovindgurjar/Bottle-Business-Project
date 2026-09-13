<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', 'JALVAN ERP')
    </title>

    @vite([
        'resources/css/admin.css',
        'resources/js/admin.js'
    ])

    @stack('styles')
    @stack('scripts')

</head>


<body>

<div
    class="admin-app"
    id="adminApp"
>

    {{-- MOBILE OVERLAY --}}

    <div
        class="sidebar-overlay"
        id="sidebarOverlay"
    ></div>


    {{-- SIDEBAR --}}

    <aside
        class="admin-sidebar"
        id="adminSidebar"
    >

        {{-- LOGO --}}

        <div class="sidebar-brand">

            <a
                href="{{ route('admin.dashboard') }}"
                class="brand-link"
            >

                <span class="brand-logo">
                    J
                </span>

                <span class="brand-text">

                    <strong>
                        JALVAN
                    </strong>

                    <small>
                        ERP
                    </small>

                </span>

            </a>


            <button
                type="button"
                class="sidebar-collapse-btn"
                id="sidebarCollapse"
                aria-label="Collapse sidebar"
            >
                ‹
            </button>

        </div>


        {{-- NAVIGATION --}}

        <nav class="sidebar-nav">


            {{-- MAIN --}}

            <div class="nav-section">

                <span>
                    Main
                </span>

            </div>


            <a
                href="{{ route('admin.dashboard') }}"
                class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <rect
                            x="3"
                            y="3"
                            width="7"
                            height="7"
                            rx="1"
                        />
                        <rect
                            x="14"
                            y="3"
                            width="7"
                            height="7"
                            rx="1"
                        />
                        <rect
                            x="3"
                            y="14"
                            width="7"
                            height="7"
                            rx="1"
                        />
                        <rect
                            x="14"
                            y="14"
                            width="7"
                            height="7"
                            rx="1"
                        />
                    </svg>
                </span>

                <span class="nav-label">
                    Dashboard
                </span>

            </a>


            {{-- SALES --}}

            <div class="nav-section">

                <span>
                    Sales
                </span>

            </div>


            <a
                href="{{ route('admin.leads.index') }}"
                class="nav-item {{ request()->routeIs('admin.leads.*') ? 'active' : '' }}"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <circle cx="9" cy="8" r="3"/>
                        <path d="M3 20c0-3 2.5-5 6-5s6 2 6 5"/>
                        <path d="M16 11c2.5.3 4 1.8 4 4"/>
                    </svg>
                </span>

                <span class="nav-label">
                    Leads
                </span>

                @if(isset($sidebarStats['new_leads']) && $sidebarStats['new_leads'] > 0)

                    <span class="nav-count">
                        {{ $sidebarStats['new_leads'] }}
                    </span>

                @endif

            </a>


            <a
                href="{{ route('admin.customers.index') }}"
                class="nav-item {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <circle cx="9" cy="8" r="3"/>
                        <circle cx="17" cy="9" r="2"/>
                        <path d="M3 20c0-3 2.5-5 6-5s6 2 6 5"/>
                        <path d="M14 16c3 0 5 1.5 5 4"/>
                    </svg>
                </span>

                <span class="nav-label">
                    Customers
                </span>

            </a>


            <a
                href="{{ route('admin.quotations.index') }}"
                class="nav-item {{ request()->routeIs('admin.quotations.*') ? 'active' : '' }}"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M6 2h12v20l-6-3-6 3z"/>
                        <path d="M9 7h6"/>
                        <path d="M9 11h6"/>
                    </svg>
                </span>

                <span class="nav-label">
                    Quotations
                </span>

            </a>


            <a
                href="{{ route('admin.orders.index') }}"
                class="nav-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M3 7h18v13H3z"/>
                        <path d="M7 7V4h10v3"/>
                        <path d="M3 11h18"/>
                    </svg>
                </span>

                <span class="nav-label">
                    Orders
                </span>

            </a>


            {{-- CATALOG --}}

            <div class="nav-section">

                <span>
                    Catalog
                </span>

            </div>


            <a
                href="{{ route('admin.products.index') }}"
                class="nav-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M8 3h8"/>
                        <path d="M9 3v4"/>
                        <path d="M15 3v4"/>
                        <path d="M7 7h10"/>
                        <path d="M6 7h12v14H6z"/>
                    </svg>
                </span>

                <span class="nav-label">
                    Products
                </span>

            </a>


            <a
                href="{{ route('admin.pricing.index') }}"
                class="nav-item {{ request()->routeIs('admin.pricing.*') ? 'active' : '' }}"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <circle cx="8" cy="8" r="3"/>
                        <path d="M14 5h7"/>
                        <path d="M14 8h5"/>
                        <path d="M3 16h18"/>
                        <path d="M3 20h12"/>
                    </svg>
                </span>

                <span class="nav-label">
                    Pricing
                </span>

            </a>


            {{-- DESIGN / PRODUCTION --}}

            <div class="nav-section">

                <span>
                    Design & Production
                </span>

            </div>


            <a
                href="{{ route('admin.designs.index') }}"
                class="nav-item {{ request()->routeIs('admin.designs.*') ? 'active' : '' }}"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 20l4-1 11-11-3-3L5 16z"/>
                        <path d="M13 6l3 3"/>
                    </svg>
                </span>

                <span class="nav-label">
                    Design Studio
                </span>

            </a>


            <a
                href="{{ route('admin.production.index') }}"
                class="nav-item  {{ request()->routeIs('admin.production.*') ? 'active' : '' }}"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 5h16v14H4z"/>
                        <path d="M8 9h8"/>
                        <path d="M8 13h5"/>
                    </svg>
                </span>

                <span class="nav-label">
                    Production
                </span>

            </a>


            <a
                href="{{ route('admin.batches.index') }}"
                class="nav-item {{ request()->routeIs('admin.batches.*') ? 'active' : '' }}"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <rect
                            x="4"
                            y="4"
                            width="16"
                            height="16"
                            rx="2"
                        />
                        <path d="M8 8h8v8H8z"/>
                    </svg>
                </span>

                <span class="nav-label">
                    Batches
                </span>

            </a>


            <a
                href="{{ route('admin.inventory.index') }}"
                class="nav-item {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 6h16"/>
                        <path d="M4 12h16"/>
                        <path d="M4 18h16"/>
                        <circle cx="8" cy="6" r="2"/>
                        <circle cx="15" cy="12" r="2"/>
                        <circle cx="10" cy="18" r="2"/>
                    </svg>
                </span>

                <span class="nav-label">
                    Inventory
                </span>

            </a>

             {{-- PROCUREMENT --}}

            <div class="nav-section">

                <span>
                    PROCUREMENT
                </span>

            </div>


            <a
                href="{{ route('admin.suppliers.index') }}"
                class="nav-item {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M3 6h11v11H3z"/>
                        <path d="M14 10h4l3 3v4h-7z"/>
                        <circle cx="7" cy="19" r="2"/>
                        <circle cx="18" cy="19" r="2"/>
                    </svg>
                </span>

                <span class="nav-label">
                    Suppliers
                </span>

            </a>

             <a
                href="{{ route('admin.purchases.index') }}"
                class="nav-item {{ request()->routeIs('admin.purchases.*') ? 'active' : '' }}"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M3 6h11v11H3z"/>
                        <path d="M14 10h4l3 3v4h-7z"/>
                        <circle cx="7" cy="19" r="2"/>
                        <circle cx="18" cy="19" r="2"/>
                    </svg>
                </span>

                <span class="nav-label">
                    Purchases
                </span>

            </a>



            {{-- DELIVERY --}}

            <div class="nav-section">

                <span>
                    Delivery
                </span>

            </div>


            <a
                href="{{ route('admin.deliveries.index') }}"
                class="nav-item {{ request()->routeIs('admin.deliveries.*') ? 'active' : '' }}"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M3 6h11v11H3z"/>
                        <path d="M14 10h4l3 3v4h-7z"/>
                        <circle cx="7" cy="19" r="2"/>
                        <circle cx="18" cy="19" r="2"/>
                    </svg>
                </span>

                <span class="nav-label">
                    Deliveries
                </span>

            </a>


            {{-- FINANCE --}}

            <div class="nav-section">

                <span>
                    Finance
                </span>

            </div>


            <a
                href="{{ route('admin.payments.index') }}"
                class="nav-item {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 7v10"/>
                        <path d="M15 9.5c0-1-1.2-1.8-3-1.8s-3 .8-3 2 1 1.8 3 2 3 1 3 2-1.2 2-3 2-3-.8-3-1.8"/>
                    </svg>
                </span>

                <span class="nav-label">
                    Payments
                </span>

            </a>


            <a
                href="{{ route('admin.invoices.index') }}"
                class="nav-item {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M5 3h14v18H5z"/>
                        <path d="M8 7h8"/>
                        <path d="M8 11h8"/>
                        <path d="M8 15h5"/>
                    </svg>
                </span>

                <span class="nav-label">
                    Invoices
                </span>

            </a>


            <a
                href="{{ route('admin.income-expenses.index') }}"
                class="nav-item {{ request()->routeIs('admin.income-expenses.*') ? 'active' : '' }}"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 20V10"/>
                        <path d="M10 20V4"/>
                        <path d="M16 20v-7"/>
                        <path d="M22 20H2"/>
                    </svg>
                </span>

                <span class="nav-label">
                    Income & Expenses
                </span>

            </a>

            <a
                href="{{ route('admin.income-expenses.categories.index') }}"
                class="nav-item {{ request()->routeIs('admin.income-expenses.categories.*') ? 'active' : '' }}"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 20V10"/>
                        <path d="M10 20V4"/>
                        <path d="M16 20v-7"/>
                        <path d="M22 20H2"/>
                    </svg>
                </span>

                <span class="nav-label">
                    Categories
                </span>

            </a>


            {{-- PEOPLE --}}

            <div class="nav-section">

                <span>
                    People
                </span>

            </div>


            <a
                href="{{ route('admin.staff.index') }}"
                class="nav-item {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="7" r="3"/>
                        <path d="M5 21c0-4 3-7 7-7s7 3 7 7"/>
                    </svg>
                </span>

                <span class="nav-label">
                    Staff
                </span>

            </a>


            <a
                href="{{ route('admin.attendance.index') }}"
                class="nav-item {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <rect
                            x="3"
                            y="5"
                            width="18"
                            height="16"
                            rx="2"
                        />
                        <path d="M7 3v4"/>
                        <path d="M17 3v4"/>
                        <path d="M3 10h18"/>
                    </svg>
                </span>

                <span class="nav-label">
                    Attendance
                </span>

            </a>


            {{-- SUPPORT --}}

            <div class="nav-section">

                <span>
                    Support
                </span>

            </div>


            <a
                href="#"
                class="nav-item"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 5h16v14H4z"/>
                        <path d="M8 9h8"/>
                        <path d="M8 13h5"/>
                    </svg>
                </span>

                <span class="nav-label">
                    Complaints / Issues
                </span>

            </a>


            {{-- ANALYTICS --}}

            <div class="nav-section">

                <span>
                    Analytics
                </span>

            </div>


            <a
                href="#"
                class="nav-item"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 19V5"/>
                        <path d="M4 19h17"/>
                        <path d="M7 15l4-4 3 2 5-7"/>
                    </svg>
                </span>

                <span class="nav-label">
                    Reports
                </span>

            </a>


            {{-- SYSTEM --}}

            <div class="nav-section">

                <span>
                    System
                </span>

            </div>


            <a
                href="#"
                class="nav-item"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="8" r="3"/>
                        <path d="M5 21c0-4 3-7 7-7s7 3 7 7"/>
                    </svg>
                </span>

                <span class="nav-label">
                    Users & Roles
                </span>

            </a>


            <a
                href="#"
                class="nav-item"
            >

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3v18"/>
                        <path d="M3 12h18"/>
                        <circle cx="12" cy="12" r="8"/>
                    </svg>
                </span>

                <span class="nav-label">
                    Settings
                </span>

            </a>

        </nav>


        {{-- SIDEBAR FOOTER --}}

        <div class="sidebar-footer">

            <div class="sidebar-status">

                <span class="status-dot"></span>

                <span class="nav-label">
                    System Online
                </span>

            </div>

        </div>

    </aside>


    {{-- MAIN CONTENT --}}

    <main class="admin-main">


        {{-- TOPBAR --}}

        <header class="admin-header">

            <div class="header-left">

                <button
                    type="button"
                    class="mobile-menu-btn"
                    id="mobileMenuBtn"
                >
                    ☰
                </button>


                <div class="breadcrumb-area">

                    <div class="breadcrumb-title">
                        @yield('page_title', 'Dashboard')
                    </div>

                    @hasSection('breadcrumb')

                        <div class="breadcrumb">
                            @yield('breadcrumb')
                        </div>

                    @endif

                </div>

            </div>


            <div class="header-right">


                {{-- SEARCH --}}

                <button
                    type="button"
                    class="header-search"
                    id="globalSearchBtn"
                >

                    <svg viewBox="0 0 24 24">
                        <circle
                            cx="11"
                            cy="11"
                            r="7"
                        />

                        <path d="m20 20-4-4"/>
                    </svg>

                    <span>
                        Search
                    </span>

                    <kbd>
                        Ctrl K
                    </kbd>

                </button>


                {{-- NOTIFICATIONS --}}

                <button
                    type="button"
                    class="header-icon-btn"
                    id="notificationBtn"
                >

                    <svg viewBox="0 0 24 24">
                        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                        <path d="M10 21h4"/>
                    </svg>

                    @if(isset($sidebarStats['notifications']) && $sidebarStats['notifications'] > 0)

                        <span class="notification-dot"></span>

                    @endif

                </button>


                {{-- USER --}}

                <div
                    class="user-menu"
                    id="userMenu"
                >

                    <button
                        type="button"
                        class="user-menu-trigger"
                    >

                        <span class="user-avatar">

                            {{ strtoupper(
                                substr(
                                    auth()->user()->name ?? 'A',
                                    0,
                                    1
                                )
                            ) }}

                        </span>


                        <span class="user-info">

                            <strong>
                                {{ auth()->user()->name ?? 'Admin' }}
                            </strong>

                            <small>
                                Administrator
                            </small>

                        </span>


                        <span class="user-chevron">
                           ⌄
                        </span>

                    </button>


                    <div class="user-dropdown">

                        <a href="#">
                            My Profile
                        </a>

                        <a href="#">
                            Account Settings
                        </a>

                        <div class="dropdown-divider"></div>

                        <form
                            method="POST"
                            action="{{ route('admin.logout') }}"
                        >

                            @csrf

                            <button type="submit">
                                Sign out
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </header>


        {{-- PAGE --}}

        <section class="admin-content">

            @if(session('success'))

                <div class="flash-message success">

                    <span>
                        {{ session('success') }}
                    </span>

                    <button
                        type="button"
                        onclick="this.parentElement.remove()"
                    >
                        ×
                    </button>

                </div>

            @endif


            @if(session('error'))

                <div class="flash-message error">

                    <span>
                        {{ session('error') }}
                    </span>

                    <button
                        type="button"
                        onclick="this.parentElement.remove()"
                    >
                        ×
                    </button>

                </div>

            @endif


            @if($errors->any())

                <div class="flash-message error">

                    <div>

                        @foreach($errors->all() as $error)

                            <div>
                                {{ $error }}
                            </div>

                        @endforeach

                    </div>

                    <button
                        type="button"
                        onclick="this.parentElement.remove()"
                    >
                        ×
                    </button>

                </div>

            @endif


            @yield('content')

        </section>

    </main>

</div>


{{-- GLOBAL SEARCH MODAL --}}

<div
    class="search-modal"
    id="searchModal"
>

    <div class="search-modal-backdrop"></div>

    <div class="search-modal-card">

        <div class="search-modal-input">

            <svg viewBox="0 0 24 24">
                <circle
                    cx="11"
                    cy="11"
                    r="7"
                />

                <path d="m20 20-4-4"/>
            </svg>

            <input
                type="text"
                id="globalSearchInput"
                placeholder="Search customers, leads, orders..."
                autocomplete="off"
            >

            <kbd>
                ESC
            </kbd>

        </div>

        <div class="search-results">

            <div class="search-empty">

                <span>
                    ⌕
                </span>

                <p>
                    Start typing to search
                </p>

            </div>

        </div>

    </div>

</div>


{{-- NOTIFICATION PANEL --}}

<div
    class="notification-panel"
    id="notificationPanel"
>

    <div class="notification-panel-header">

        <div>

            <strong>
                Notifications
            </strong>

            <span>
                Stay updated with your business
            </span>

        </div>

        <button>
            Mark all read
        </button>

    </div>


    <div class="notification-list">

        <div class="notification-empty">

            <div class="notification-empty-icon">
                ✓
            </div>

            <strong>
                You're all caught up
            </strong>

            <span>
                No new notifications
            </span>

        </div>

    </div>

</div>


</body>
</html>