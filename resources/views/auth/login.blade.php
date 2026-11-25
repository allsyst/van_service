<x-guest-layout>

    @if (session('status'))
        <div class="auth-alert auth-alert-success">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="auth-alert auth-alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label><i class="fas fa-envelope"></i>Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" required autofocus autocomplete="username" class="form-control-custom" />
        </div>

        <div class="form-group">
            <label><i class="fas fa-lock"></i>Password</label>
            <input type="password" name="password" placeholder="Enter your password" required autocomplete="current-password" class="form-control-custom" />
        </div>

        <div class="auth-footer-row">
            <label class="remember-label">
                <input type="checkbox" name="remember">
                Remember me
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
            @endif
        </div>

        <button type="submit" class="btn-login">
            <i class="fas fa-sign-in-alt" style="margin-right:8px;"></i>Log In
        </button>
    </form>

    <div class="divider">or</div>

    <div style="text-align:center; margin-bottom:0.5rem; font-size:0.83rem; color:#6b7280;">
        Don't have an account yet?
    </div>
    <a href="{{ route('register') }}" class="btn-register">
        <i class="fas fa-user-plus" style="margin-right:8px;"></i>Create an Account
    </a>

</x-guest-layout>
