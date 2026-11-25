<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'VanService') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #0f1f4b 0%, #1b2f6e 40%, #1565c0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow-x: hidden;
        }
        /* Background decoration */
        body::before {
            content: '';
            position: fixed;
            top: -30%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255,255,255,.05) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        body::after {
            content: '';
            position: fixed;
            bottom: -20%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(100,150,255,.08) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        /* Auth Card */
        .auth-wrapper {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }
        .auth-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 25px 80px rgba(0,0,0,.35);
            overflow: hidden;
        }
        .auth-header {
            background: linear-gradient(135deg, #1a237e, #3949ab, #1565c0);
            padding: 2rem 2rem 1.75rem;
            text-align: center;
            color: #fff;
            position: relative;
        }
        .auth-header .brand-icon {
            width: 68px; height: 68px;
            background: rgba(255,255,255,.15);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.8rem;
            border: 2px solid rgba(255,255,255,.25);
        }
        .auth-header h1 {
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }
        .auth-header p {
            font-size: 0.82rem;
            opacity: 0.75;
        }
        .auth-body {
            padding: 2rem;
        }

        /* Form elements */
        .form-group { margin-bottom: 1rem; }
        .form-group label {
            font-size: 0.82rem;
            font-weight: 700;
            color: #374151;
            display: block;
            margin-bottom: 0.4rem;
        }
        .form-group label i { color: #3949ab; margin-right: 5px; }
        .form-control-custom {
            width: 100%;
            padding: 0.65rem 0.9rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.9rem;
            color: #111827;
            font-family: 'Nunito', sans-serif;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
            background: #fafafa;
        }
        .form-control-custom:focus {
            border-color: #3949ab;
            box-shadow: 0 0 0 3px rgba(57,73,171,.12);
            background: #fff;
        }
        select.form-control-custom { cursor: pointer; }

        /* Buttons */
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #1a237e, #3949ab);
            color: #fff;
            border: none;
            padding: 0.8rem;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
            letter-spacing: 0.3px;
            font-family: 'Nunito', sans-serif;
        }
        .btn-login:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-login:active { transform: translateY(0); }
        .btn-register {
            width: 100%;
            background: #fff;
            color: #3949ab;
            border: 2px solid #3949ab;
            padding: 0.75rem;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: block;
            text-align: center;
            font-family: 'Nunito', sans-serif;
        }
        .btn-register:hover { background: #3949ab; color: #fff; text-decoration: none; }

        /* Misc */
        .divider {
            display: flex; align-items: center; gap: 0.75rem;
            margin: 1.25rem 0;
            color: #9ca3af; font-size: 0.78rem;
        }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e5e7eb; }
        .forgot-link { color: #3949ab; font-size: 0.8rem; text-decoration: none; font-weight: 600; }
        .forgot-link:hover { text-decoration: underline; }
        .remember-label { display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 0.83rem; color: #6b7280; font-weight: 400; }
        .remember-label input { accent-color: #3949ab; width: auto; }
        .auth-footer-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }

        /* Alerts */
        .auth-alert {
            padding: 0.7rem 1rem;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex; align-items: flex-start; gap: 0.5rem;
        }
        .auth-alert-success { background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; }
        .auth-alert-error { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }

        /* Responsive */
        @media (max-width: 480px) {
            .auth-body { padding: 1.5rem; }
            .auth-header { padding: 1.5rem; }
            .auth-header h1 { font-size: 1.3rem; }
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <div class="brand-icon">
                    <i class="fas fa-shuttle-van"></i>
                </div>
                <h1>VanService</h1>
                <p>Northern Iloilo State University</p>
            </div>
            <div class="auth-body">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
