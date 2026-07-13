<header class="app-header">
    <div class="d-flex align-items-center">
        <button class="btn btn-link text-body d-lg-none p-0 me-3" id="sidebarToggle">
            <i class="fa-solid fa-bars fs-4"></i>
        </button>
        <h2 class="header-title d-none d-md-block">Overview</h2>
    </div>

    <div class="d-flex align-items-center gap-3">
        <div class="position-relative d-flex align-items-center gap-1">
            <button class="btn btn-link text-muted p-2" id="darkModeToggle" title="Toggle Dark Mode">
                <i class="fa-regular fa-moon fs-5" id="darkModeIcon"></i>
            </button>
            <button class="btn btn-link text-muted p-2" id="aiAssistantToggle" title="AI Assistant Floating Widget">
                <i class="fa-solid fa-robot fs-5"></i>
            </button>
        </div>

        <div class="dropdown">
            <div class="user-profile" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="avatar">
                    @if(Auth::user()->profile_image)
                        <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="Profile" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                    @else
                        {{ substr(Auth::user()->name ?? 'Admin', 0, 1) }}
                    @endif
                </div>
                <div class="d-none d-md-block text-start">
                    <div class="fw-semibold text-body lh-1" style="font-size: 0.9rem;">{{ Auth::user()->name ?? 'Admin User' }}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">Manager</div>
                </div>
                <i class="fa-solid fa-chevron-down text-muted ms-2" style="font-size: 0.8rem;"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-2">
                <li><a class="dropdown-item py-2" href="{{ route('profile.edit') }}"><i class="fa-regular fa-user me-2"></i> Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item py-2 text-danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

