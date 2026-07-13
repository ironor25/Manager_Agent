<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Login - Manager Agent</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            color: #0f172a;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 24px;
            padding: 48px;
            box-shadow: 0 25px 50px -12px rgba(14, 165, 233, 0.15);
            max-width: 450px;
            width: 100%;
            margin: 20px;
        }

        .login-logo {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            margin: 0 auto 24px auto;
            box-shadow: 0 10px 15px -3px rgba(14, 165, 233, 0.2);
        }

        .form-control {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 16px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1);
            background-color: #ffffff;
        }

        .input-group-text {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #64748b;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            border: none;
            color: white;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(14, 165, 233, 0.3);
            background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
        }

        .toast-container { z-index: 1055; }

        /* Custom global cursor styles */
        a, button, input[type="button"], input[type="submit"], input[type="checkbox"], input[type="radio"], [role="button"], label {
            cursor: pointer !important;
        }
        input[type="text"], input[type="password"], input[type="email"], input:not([type]) {
            cursor: text !important;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="text-center mb-4">
            <div class="login-logo">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <h2 class="fw-bold mb-2">Welcome Back</h2>
            <p class="text-muted">Enter your credentials to access the workspace</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 12px; background: #fee2e2; color: #b91c1c;">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold text-body">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text border-end-0 rounded-start-3"><i class="fa-solid fa-envelope text-muted"></i></span>
                    <input type="email" name="email" class="form-control border-start-0 rounded-end-3" placeholder="admin@manager.com" required value="{{ old('email') }}">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold text-body">Password</label>
                <div class="input-group">
                    <span class="input-group-text border-end-0 rounded-start-3"><i class="fa-solid fa-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control border-start-0 rounded-end-3" placeholder="••••••••" required>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember" style="border-color: #cbd5e1;">
                    <label class="form-check-label text-muted" for="remember">Remember me</label>
                </div>
            </div>

            <button type="submit" class="btn-primary-custom">
                Sign In <i class="fa-solid fa-arrow-right-to-bracket ms-2"></i>
            </button>
        </form>
    </div>

    <!-- Global Toast Notification -->
    @if(session('success'))
    <div class="toast-container position-fixed bottom-0 end-0 p-4">
        <div id="successToast" class="toast align-items-center text-white bg-success border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2000">
            <div class="d-flex">
                <div class="toast-body fw-medium py-3 px-4" style="font-size: 0.95rem;">
                    <i class="fa-solid fa-check-circle me-2"></i> {{ session('success') }}
                </div>
            </div>
        </div>
    </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toastEl = document.getElementById('successToast');
            if (toastEl) {
                var toast = new bootstrap.Toast(toastEl);
                toast.show();
            }
        });
    </script>
</body>
</html>

