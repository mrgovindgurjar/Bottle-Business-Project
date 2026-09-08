<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>JALVAN ERP — Login</title>

    @vite([
        'resources/css/admin.css',
        'resources/js/admin.js'
    ])
 
</head>

<body class="auth-page">

<div class="auth-shell">

    <div class="auth-background"></div>

    <div class="auth-container">

        {{-- BRAND SIDE --}}

        <div class="auth-brand">

            <div class="brand-mark">
                J
            </div>

            <div class="brand-name">
                JALVAN
            </div>

            <div class="brand-subtitle">
                Business Management ERP
            </div>

            <div class="brand-description">
                Manage customers, orders, production,
                inventory and finance from one place.
            </div>

            <div class="brand-feature">

                <div>✓ Sales & CRM</div>
                <div>✓ Production Management</div>
                <div>✓ Inventory & Batch Tracking</div>
                <div>✓ Finance & Reports</div>

            </div>

        </div>


        {{-- LOGIN CARD --}}

        <div class="login-card">

            <div class="mobile-brand">
                JALVAN ERP
            </div>

            <div class="login-header">

                <h1>
                    Welcome back
                </h1>

                <p>
                    Sign in to your admin account
                </p>

            </div>


            @if($errors->any())

                <div class="auth-error">

                    {{ $errors->first() }}

                </div>

            @endif


            <form
                method="POST"
                action="{{ route('admin.login.store') }}"
            >

                @csrf


                <div class="form-group">

                    <label class="form-label">
                        Email or Mobile
                    </label>

                    <input
                        type="text"
                        name="login"
                        value="{{ old('login') }}"
                        class="form-control"
                        placeholder="Enter email or mobile"
                        autocomplete="username"
                        autofocus
                    >

                </div>


                <div class="form-group">

                    <label class="form-label">
                        Password
                    </label>

                    <div class="password-field">

                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            onclick="togglePassword()"
                        >
                            Show
                        </button>

                    </div>

                </div>


                <div class="login-options">

                    <label class="remember-me">

                        <input
                            type="checkbox"
                            name="remember"
                        >

                        <span>
                            Remember me
                        </span>

                    </label>

                    <a href="#">
                        Forgot password?
                    </a>

                </div>


                <button
                    type="submit"
                    class="login-button"
                >

                    <span>
                        Sign in
                    </span>

                    <span class="login-arrow">
                        →
                    </span>

                </button>

            </form>


            <div class="login-footer">

                JALVAN ERP

                <span>•</span>

                Secure Business Management

            </div>

        </div>

    </div>

</div>

</body>

</html>