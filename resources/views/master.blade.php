<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <script>
        const theme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', theme);
    </script>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Manager Agent - Premium Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --background: #f8fafc;
            --surface: linear-gradient(135deg, #ffffff 0%, #f5f3ff 100%);
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --sidebar-width: 260px;
            --header-height: 70px;
            --table-hover-bg: #f1f5f9;
            --table-th-bg: #f8fafc;
            --profile-hover-bg: #f1f5f9;
        }

        [data-bs-theme="dark"] {
            --background: #0f172a;
            --surface: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --primary: #818cf8;
            --primary-dark: #6366f1;
            --table-hover-bg: rgba(255, 255, 255, 0.05);
            --table-th-bg: rgba(0, 0, 0, 0.2);
            --profile-hover-bg: rgba(255, 255, 255, 0.1);
        }

        [data-bs-theme="dark"] .bg-light {
            background-color: var(--table-th-bg) !important;
            color: var(--text-main) !important;
        }

        [data-bs-theme="dark"] .text-muted {
            color: var(--text-muted) !important;
        }
        
        [data-bs-theme="dark"] .form-control, 
        [data-bs-theme="dark"] .form-select {
            background-color: #0f172a !important;
            border-color: var(--border-color) !important;
            color: var(--text-main) !important;
        }
        
        [data-bs-theme="dark"] canvas {
            filter: invert(0.9) hue-rotate(180deg);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            color: var(--text-main);
            overflow-x: hidden;
            margin: 0;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .app-sidebar {
            width: var(--sidebar-width);
            background: var(--surface);
            border-right: 1px solid var(--border-color);
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1040;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 10px rgba(0,0,0,0.02);
        }
        
        .sidebar-header {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 24px;
            border-bottom: 1px solid var(--border-color);
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--primary);
        }

        .sidebar-menu {
            padding: 20px 0;
            flex: 1;
            overflow-y: auto;
        }

        .menu-item {
            padding: 12px 24px;
            display: flex;
            align-items: center;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            margin: 4px 16px;
            border-radius: 8px;
        }

        .menu-item:hover, .menu-item.active {
            background: rgba(79, 70, 229, 0.08);
            color: var(--primary);
        }

        .menu-item.text-danger:hover {
            background: rgba(239, 68, 68, 0.08) !important;
            color: var(--danger) !important;
        }

        .menu-item i {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        /* Main Content Wrapper */
        .app-wrapper {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: all 0.3s ease;
            width: calc(100% - var(--sidebar-width));
        }

        /* Header Styles */
        .app-header {
            height: var(--header-height);
            background: var(--surface);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 1030;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }

        .header-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--text-main);
            margin: 0;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            padding: 6px 12px;
            border-radius: 50px;
            transition: background 0.2s;
        }

        .user-profile:hover {
            background: var(--profile-hover-bg);
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* Main Content Area */
        .main-content {
            flex: 1;
            padding: 32px;
        }

        /* Footer */
        .app-footer {
            background: var(--surface);
            border-top: 1px solid var(--border-color);
            padding: 20px 32px;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* Global UI Elements */
        .bg-white { background: var(--surface) !important; }

        .card {
            background: var(--surface);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
            margin-bottom: 24px;
            overflow: hidden;
        }

        .card-header {
            background: var(--surface);
            border-bottom: 1px solid var(--border-color);
            padding: 20px 24px;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .card-body {
            padding: 24px;
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        /* Tables */
        .table {
            margin-bottom: 0;
            width: 100% !important;
        }
        
        .table th {
            background: var(--table-th-bg);
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 16px;
            border-bottom: 2px solid var(--border-color);
        }
        
        .table td {
            padding: 16px;
            vertical-align: middle;
            color: var(--text-main);
            border-bottom: 1px solid var(--border-color);
        }

        .table-hover tbody tr:hover {
            background-color: var(--table-hover-bg);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Toast & Modals */
        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
        }
        
        .modal-header {
            border-bottom: 1px solid var(--border-color);
            padding: 24px;
        }
        
        .modal-body {
            padding: 24px;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .app-sidebar {
                transform: translateX(-100%);
            }
            .app-sidebar.show {
                transform: translateX(0);
            }
            .app-wrapper {
                margin-left: 0;
                width: 100%;
            }
            .sidebar-overlay {
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 1035;
                display: none;
            }
            .sidebar-overlay.show {
                display: block;
            }
        }
    </style>
    @stack('page-style')
</head>
<body>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    @include('sidebar')

    <div class="app-wrapper">
        @include('header')

        <main class="main-content">
            @yield('page-content')
        </main>

        @include('footer')
    </div>

    <!-- jQuery & Bootstrap Bundle with Popper -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Sidebar Toggle Logic
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        });

        document.getElementById('sidebarOverlay')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('show');
            this.classList.remove('show');
        });
        
        // Dark Mode Toggle Logic
        document.getElementById('darkModeToggle')?.addEventListener('click', function() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            const icon = document.getElementById('darkModeIcon');
            if(icon) {
                if(newTheme === 'dark') {
                    icon.classList.replace('fa-moon', 'fa-sun');
                } else {
                    icon.classList.replace('fa-sun', 'fa-moon');
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const theme = document.documentElement.getAttribute('data-bs-theme');
            const icon = document.getElementById('darkModeIcon');
            if(icon && theme === 'dark') {
                icon.classList.replace('fa-moon', 'fa-sun');
            }
        });

        // Setup CSRF Token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    
    <!-- Global Toast Notification -->
    @if(session('success'))
    <div class="toast-container position-fixed bottom-0 end-0 p-4" style="z-index: 1055;">
        <div id="successToast" class="toast align-items-center text-white bg-success border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2000">
            <div class="d-flex">
                <div class="toast-body fw-medium py-3 px-4" style="font-size: 0.95rem;">
                    <i class="fa-solid fa-check-circle me-2"></i> {{ session('success') }}
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toastEl = document.getElementById('successToast');
            if (toastEl) {
                var toast = new bootstrap.Toast(toastEl);
                toast.show();
            }
        });
    </script>
    @endif

    @stack('page-script')
</body>
</html>


