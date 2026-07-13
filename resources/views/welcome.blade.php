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
            background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            color: #0f172a;
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 24px;
            padding: 56px 48px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(14, 165, 233, 0.15);
            max-width: 500px;
            width: 100%;
            margin: 20px;
        }

        .icon-container {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
            color: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 32px;
            box-shadow: 0 10px 20px -5px rgba(14, 165, 233, 0.3);
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            border: none;
            color: white;
            padding: 14px 36px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 6px -1px rgba(14, 165, 233, 0.2);
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(14, 165, 233, 0.3);
            background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
            color: white;
        }
        
        .welcome-title {
            font-size: 2rem;
            letter-spacing: -0.02em;
        }

        /* Custom global cursor styles */
        a, button, input[type="button"], input[type="submit"], [role="button"], label {
            cursor: pointer !important;
        }
    </style>
</head>
<body>

    <div class="welcome-card">
        <div class="icon-container">
            <i class="fa-solid fa-chart-pie"></i>
        </div>
        <h1 class="fw-bold mb-3 welcome-title">Workspace Login</h1>
        <p class="text-muted mb-4 fs-5" style="line-height: 1.6;">Streamline your team's performance, track tasks, and manage resources all in one premium dashboard.</p>
        
        <a href="{{ route('login') }}" class="btn-primary-custom">
            Enter Workspace <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

</body>
</html>

