<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @php
    $organization = \App\Models\Organization::first();
    @endphp
    
    <title>{{ $organization->name ?? 'Members Management System' }} - Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        body {
            background: linear-gradient(135deg, #1e3a5f 0%, #0f2440 100%);
            min-height: 100vh;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }
        .login-header .logo-img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            background: white;
            border-radius: 10px;
            padding: 5px;
        }
        .login-header h4 {
            margin-top: 15px;
            font-weight: 600;
        }
        .login-body {
            padding: 30px;
        }
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #ddd;
        }
        .form-control:focus {
            border-color: #1e3a5f;
            box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1);
        }
        .btn-login {
            background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #2c5282 0%, #1e3a5f 100%);
        }
        .welcome-text {
            color: rgba(255,255,255,0.9);
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5">
                <div class="login-card">
                    <div class="login-header">
                        @php
                        $organization = \App\Models\Organization::first();
                        @endphp
                        
                        @if($organization && $organization->logo)
                        <img src="{{ asset('storage/' . $organization->logo) }}" alt="Logo" class="logo-img">
                        @else
                        <i class="fas fa-users fa-3x"></i>
                        @endif
                        
                        <h4>{{ $organization->name ?? 'Members Management System' }}</h4>
                        <p class="welcome-text">Welcome to Members Management System</p>
                    </div>
                    
                    <div class="login-body">
                        <x-auth-session-status class="alert alert-info mb-3" :status="session('status')" />
                        
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            
                            <div class="mb-3">
                                <label class="form-label">Username or Email</label>
                                <input type="text" name="login" class="form-control" value="{{ old('login') }}" required autofocus placeholder="Enter username or email">
                                @error('login')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required placeholder="Enter password">
                                @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember_me" name="remember">
                                <label class="form-check-label" for="remember_me">Remember me</label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-login">
                                <i class="fas fa-sign-in-alt me-2"></i> Log In
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
