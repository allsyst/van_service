<x-guest-layout>

    @if ($errors->any())
        <div class="auth-alert auth-alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div style="text-align:center; margin-bottom:1.5rem;">
        <h2 style="font-size:1.2rem; font-weight:800; color:#1a237e; margin:0;">Create Your Account</h2>
        <p style="font-size:0.82rem; color:#6b7280; margin:0.25rem 0 0;">Fill in the details below to register</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-group">
            <label><i class="fas fa-user"></i>Full Name</label>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="Enter your full name" required autofocus autocomplete="name" class="form-control-custom" />
            @error('name')<div style="color:#dc2626;font-size:0.78rem;margin-top:4px;">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label><i class="fas fa-briefcase"></i>Position / Department</label>
            <input type="text" name="position" value="{{ old('position') }}" placeholder="e.g. Staff, Nurse, Engineer" required autocomplete="off" class="form-control-custom" />
            @error('position')<div style="color:#dc2626;font-size:0.78rem;margin-top:4px;">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label><i class="fas fa-envelope"></i>Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" required autocomplete="username" class="form-control-custom" />
            @error('email')<div style="color:#dc2626;font-size:0.78rem;margin-top:4px;">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label><i class="fas fa-lock"></i>Password</label>
            <input type="password" name="password" placeholder="Create a password" required autocomplete="new-password" class="form-control-custom" />
            @error('password')<div style="color:#dc2626;font-size:0.78rem;margin-top:4px;">{{ $message }}</div>@enderror
        </div>

        <div class="form-group" style="margin-bottom:1.5rem;">
            <label><i class="fas fa-lock"></i>Confirm Password</label>
            <input type="password" name="password_confirmation" placeholder="Repeat your password" required autocomplete="new-password" class="form-control-custom" />
            @error('password_confirmation')<div style="color:#dc2626;font-size:0.78rem;margin-top:4px;">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn-login">
            <i class="fas fa-user-plus" style="margin-right:8px;"></i>Register
        </button>
    </form>

    <div class="divider">or</div>

    <div style="text-align:center; margin-bottom:0.5rem; font-size:0.83rem; color:#6b7280;">
        Already have an account?
    </div>
    <a href="{{ route('login') }}" class="btn-register">
        <i class="fas fa-sign-in-alt" style="margin-right:8px;"></i>Back to Login
    </a>

</x-guest-layout>
