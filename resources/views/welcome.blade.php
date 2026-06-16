<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Welcome - Manager Agent</title>
    
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
            background: linear-gradient(135deg, #ffffff 0%, #f5f3ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            color: #0f172a;
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 24px;
            padding: 48px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(79, 70, 229, 0.08);
            max-width: 500px;
            width: 100%;
            margin: 20px;
        }

        .icon-container {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            color: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 24px;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            border: none;
            color: white;
            padding: 12px 32px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
            color: white;
        }
    </style>
</head>
<body>

    <div class="welcome-card">
        <div class="icon-container">
            <i class="fa-solid fa-briefcase"></i>
        </div>
        <h1 class="fw-bold mb-3">Manager Agent Login</h1>
        <p class="text-muted mb-4 fs-5">Streamline your team's performance, track tasks, and manage resources all in one premium dashboard.</p>
        
        <a href="{{ route('login') }}" class="btn-primary-custom">
            Go to Login <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

</body>
</html>

