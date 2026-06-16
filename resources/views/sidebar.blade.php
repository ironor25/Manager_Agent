<aside class="app-sidebar" id="sidebar">
    <div class="sidebar-header">
        <i class="fa-solid fa-shield-halved me-2"></i>
        <span>Manager Agent</span>
    </div>
    
    <div class="sidebar-menu">
        <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-house"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('employees.index') }}" class="menu-item {{ request()->routeIs('employees.index') ? 'active' : '' }}">
            <i class="fa-solid fa-user-group"></i>
            <span>Employees</span>
        </a>
        <a href="{{ route('teams.dashboard') }}" class="menu-item {{ request()->routeIs('teams.dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-users"></i>
            <span>Team Performance</span>
        </a>
        <a href="{{ route('tasks.index') }}" class="menu-item {{ request()->routeIs('tasks.index') ? 'active' : '' }}">
            <i class="fa-solid fa-list-check"></i>
            <span>Tasks</span>
        </a>
        <a href="{{ route('developer-tools.index') }}" class="menu-item {{ request()->routeIs('developer-tools.index') ? 'active' : '' }}">
            <i class="fa-solid fa-code"></i>
            <span>Developer Tools</span>
        </a>
        <a href="{{ route('commits.index') }}" class="menu-item {{ request()->routeIs('commits.index') ? 'active' : '' }}">
            <i class="fa-brands fa-git-alt"></i>
            <span>GitHub Commits</span>
        </a>
        <a href="{{ route('meetings.index') }}" class="menu-item {{ request()->routeIs('meetings.index') ? 'active' : '' }}">
            <i class="fa-solid fa-users-viewfinder"></i>
            <span>Meetings</span>
        </a>
    </div>

    <!-- Sidebar Footer / Logout -->
    <div class="sidebar-footer mt-auto border-top p-3">
        <form action="{{ route('logout') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="menu-item text-danger border-0 bg-transparent w-100 d-flex align-items-center" style="margin: 0; outline: none; background: none; text-align: left; justify-content: flex-start;">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>

