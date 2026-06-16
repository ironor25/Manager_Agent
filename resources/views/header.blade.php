<header class="app-header">
    <div class="d-flex align-items-center">
        <button class="btn btn-link text-body d-lg-none p-0 me-3" id="sidebarToggle">
            <i class="fa-solid fa-bars fs-4"></i>
        </button>
        <h2 class="header-title d-none d-md-block">Overview</h2>
    </div>

    <div class="d-flex align-items-center gap-3">
        <div class="position-relative">
            <button class="btn btn-link text-muted p-2" id="darkModeToggle" title="Toggle Dark Mode">
                <i class="fa-regular fa-moon fs-5" id="darkModeIcon"></i>
            </button>
        </div>

        <div class="position-relative">
            <button class="btn btn-link text-muted p-2">
                <i class="fa-regular fa-bell fs-5"></i>
                <span class="position-absolute top-0 end-0 translate-middle p-1 bg-danger border border-light rounded-circle">
                    <span class="visually-hidden">New alerts</span>
                </span>
            </button>
        </div>

        <div class="dropdown">
            <div class="user-profile" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="avatar">
                    {{ substr(Auth::user()->name ?? 'Admin', 0, 1) }}
                </div>
                <div class="d-none d-md-block text-start">
                    <div class="fw-semibold text-body lh-1" style="font-size: 0.9rem;">{{ Auth::user()->name ?? 'Admin User' }}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">Manager</div>
                </div>
                <i class="fa-solid fa-chevron-down text-muted ms-2" style="font-size: 0.8rem;"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-2">
                <li><a class="dropdown-item py-2" href="#"><i class="fa-regular fa-user me-2"></i> Profile</a></li>
                <li><a class="dropdown-item py-2" href="#"><i class="fa-solid fa-gear me-2"></i> Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item py-2 text-danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Logout
                    </a>
                    <form id="logout-form"  method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

