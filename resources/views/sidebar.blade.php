
<aside class="app-sidebar" id="sidebar">
    <div class="sidebar-header">
        
        <img alt="Meet New Friends on Site Name" src="https://collab.salaryslip.co/assets/files/logos/chat_page_logo_dark_mode.png?cache=1768372057">
    </div>
    
    <div class="sidebar-menu">
        @if(auth()->user()->role === 'admin')
        <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-house"></i>
            <span>Dashboard</span>
        </a>
        <div class="menu-dropdown">
            <a href="javascript:void(0);" class="menu-item d-flex align-items-center justify-content-between {{ request()->routeIs('employees.*') ? 'active' : '' }}" id="employees-menu-toggle">
                <span class="d-flex align-items-center">
                    <i class="fa-solid fa-users me-2" style="width: 20px; text-align: center;"></i>
                    <span>Employees</span>
                </span>
                <span class="chevron-toggle-btn p-1">
                    <i class="fa-solid fa-chevron-down submenu-chevron {{ request()->routeIs('employees.*') ? 'rotate-chevron' : '' }}" style="font-size: 0.75rem;"></i>
                </span>
            </a>
            <div class="submenu-container {{ request()->routeIs('employees.*') ? 'show' : '' }}" id="employees-submenu">
                <a href="{{ route('employees.index') }}" class="menu-item submenu-item {{ request()->routeIs('employees.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-address-card me-2"></i>
                    <span>Management</span>
                </a>
                <a href="{{ route('employees.analytics') }}" class="menu-item submenu-item {{ request()->routeIs('employees.analytics') ? 'active' : '' }}">
                    <i class="fa-solid fa-brain me-2"></i>
                    <span>Analytics</span>
                </a>
            </div>
        </div>
        <div class="menu-dropdown">
            <a href="javascript:void(0);" class="menu-item d-flex align-items-center justify-content-between {{ request()->routeIs('leaderboard.*') ? 'active' : '' }}" id="leaderboard-menu-toggle">
                <span class="d-flex align-items-center">
                    <i class="fa-solid fa-medal me-2" style="width: 20px; text-align: center;"></i>
                    <span>Leaderboard</span>
                </span>
                <span class="chevron-toggle-btn p-1">
                    <i class="fa-solid fa-chevron-down submenu-chevron {{ request()->routeIs('leaderboard.*') ? 'rotate-chevron' : '' }}" style="font-size: 0.75rem;"></i>
                </span>
            </a>
            <div class="submenu-container {{ request()->routeIs('leaderboard.*') ? 'show' : '' }}" id="leaderboard-submenu">
                <a href="{{ route('leaderboard.individual') }}" class="menu-item submenu-item {{ request()->routeIs('leaderboard.individual') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-ninja me-2"></i>
                    <span>Individual</span>
                </a>
                <a href="{{ route('leaderboard.team') }}" class="menu-item submenu-item {{ request()->routeIs('leaderboard.team') || request()->routeIs('leaderboard.team.details') ? 'active' : '' }}">
                    <i class="fa-solid fa-users-rays me-2"></i>
                    <span>Team</span>
                </a>
                <a href="{{ route('leaderboard.organization') }}" class="menu-item submenu-item {{ request()->routeIs('leaderboard.organization') ? 'active' : '' }}">
                    <i class="fa-solid fa-building me-2"></i>
                    <span>Organization</span>
                </a>
            </div>
        </div>
        <div class="menu-dropdown">
            <a href="{{ route('teams.management') }}" class="menu-item d-flex align-items-center justify-content-between {{ request()->routeIs('teams.*') ? 'active' : '' }}" id="teams-menu-toggle">
                <span class="d-flex align-items-center">
                    <i class="fa-solid fa-users me-2" style="width: 20px; text-align: center;"></i>
                    <span>Team</span>
                </span>
                <span class="chevron-toggle-btn p-1">
                    <i class="fa-solid fa-chevron-down submenu-chevron {{ request()->routeIs('teams.*') ? 'rotate-chevron' : '' }}" style="font-size: 0.75rem;"></i>
                </span>
            </a>
            <div class="submenu-container {{ request()->routeIs('teams.*') ? 'show' : '' }}" id="teams-submenu">
                <a href="{{ route('teams.management') }}" class="menu-item submenu-item {{ request()->routeIs('teams.management') || request()->routeIs('teams.show') ? 'active' : '' }}">
                    <i class="fa-solid fa-users-gear me-2"></i>
                    <span>Management</span>
                </a>
                <a href="{{ route('teams.dashboard') }}" class="menu-item submenu-item {{ request()->routeIs('teams.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line me-2"></i>
                    <span>Performance</span>
                </a>
            </div>
        </div>
        <div class="menu-dropdown">
            <a href="javascript:void(0);" class="menu-item d-flex align-items-center justify-content-between {{ request()->routeIs('projects.*') ? 'active' : '' }}" id="projects-menu-toggle">
                <span class="d-flex align-items-center">
                    <i class="fa-solid fa-diagram-project me-2" style="width: 20px; text-align: center;"></i>
                    <span>Projects</span>
                </span>
                <span class="chevron-toggle-btn p-1">
                    <i class="fa-solid fa-chevron-down submenu-chevron {{ request()->routeIs('projects.*') ? 'rotate-chevron' : '' }}" style="font-size: 0.75rem;"></i>
                </span>
            </a>
            <div class="submenu-container {{ request()->routeIs('projects.*') ? 'show' : '' }}" id="projects-submenu">
                <a href="{{ route('projects.index') }}" class="menu-item submenu-item {{ request()->routeIs('projects.index') || request()->routeIs('projects.show') ? 'active' : '' }}">
                    <i class="fa-solid fa-folder-open me-2"></i>
                    <span>Management</span>
                </a>
                <a href="{{ route('projects.monitoring') }}" class="menu-item submenu-item {{ request()->routeIs('projects.monitoring') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie me-2"></i>
                    <span>Monitoring</span>
                </a>
                <a href="{{ route('projects.reports') }}" class="menu-item submenu-item {{ request()->routeIs('projects.reports') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice me-2"></i>
                    <span>Reports</span>
                </a>
            </div>
        </div>
        <div class="menu-dropdown">
            <a href="javascript:void(0);" class="menu-item d-flex align-items-center justify-content-between {{ request()->routeIs('tasks.*') ? 'active' : '' }}" id="tasks-menu-toggle">
                <span class="d-flex align-items-center">
                    <i class="fa-solid fa-list-check me-2" style="width: 20px; text-align: center;"></i>
                    <span>Tasks</span>
                </span>
                <span class="chevron-toggle-btn p-1">
                    <i class="fa-solid fa-chevron-down submenu-chevron {{ request()->routeIs('tasks.*') ? 'rotate-chevron' : '' }}" style="font-size: 0.75rem;"></i>
                </span>
            </a>
            <div class="submenu-container {{ request()->routeIs('tasks.*') ? 'show' : '' }}" id="tasks-submenu">
                <a href="{{ route('tasks.index') }}" class="menu-item submenu-item {{ request()->routeIs('tasks.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-tasks me-2"></i>
                    <span>Management</span>
                </a>
                <a href="{{ route('tasks.monitoring') }}" class="menu-item submenu-item {{ request()->routeIs('tasks.monitoring') ? 'active' : '' }}">
                    <i class="fa-solid fa-desktop me-2"></i>
                    <span>Monitoring</span>
                </a>
                <a href="{{ route('tasks.metrics') }}" class="menu-item submenu-item {{ request()->routeIs('tasks.metrics') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line me-2"></i>
                    <span>Metrics</span>
                </a>
            </div>
        </div>
        <div class="menu-dropdown">
            <a href="javascript:void(0);" class="menu-item d-flex align-items-center justify-content-between {{ request()->routeIs('attendance.*') ? 'active' : '' }}" id="attendance-menu-toggle">
                <span class="d-flex align-items-center">
                    <i class="fa-solid fa-clock me-2" style="width: 20px; text-align: center;"></i>
                    <span>Attendance</span>
                </span>
                <span class="chevron-toggle-btn p-1">
                    <i class="fa-solid fa-chevron-down submenu-chevron {{ request()->routeIs('attendance.*') ? 'rotate-chevron' : '' }}" style="font-size: 0.75rem;"></i>
                </span>
            </a>
            <div class="submenu-container {{ request()->routeIs('attendance.*') ? 'show' : '' }}" id="attendance-submenu">
                <a href="{{ route('attendance.index') }}" class="menu-item submenu-item {{ request()->routeIs('attendance.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-check me-2"></i>
                    <span>Management</span>
                </a>
                <a href="{{ route('attendance.analytics') }}" class="menu-item submenu-item {{ request()->routeIs('attendance.analytics') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line me-2"></i>
                    <span>Analytics</span>
                </a>
                <a href="{{ route('attendance.reports') }}" class="menu-item submenu-item {{ request()->routeIs('attendance.reports') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice me-2"></i>
                    <span>Reports</span>
                </a>
            </div>
        </div>
        <a href="{{ route('workload.index') }}" class="menu-item {{ request()->routeIs('workload.index') ? 'active' : '' }}">
            <i class="fa-solid fa-weight-scale"></i>
            <span>Workload Analysis</span>
        </a>

        <a href="{{ route('ai-agent.index') }}" class="menu-item {{ request()->routeIs('ai-agent.*') ? 'active' : '' }}">
            <i class="fa-solid fa-robot" style="color: #6366f1;"></i>
            <span style="font-weight: 600; color: #6366f1;">AI Chatbot</span>
        </a>

        <a href="{{ route('developer-tools.index') }}" class="menu-item {{ request()->routeIs('developer-tools.index') ? 'active' : '' }}">
            <i class="fa-solid fa-code"></i>
            <span>Developer Tools</span>
        </a>
        <a href="{{ route('commits.index') }}" class="menu-item {{ request()->routeIs('commits.index') ? 'active' : '' }}">
            <i class="fa-brands fa-github"></i>
            <span>GitHub Commits</span>
        </a>
        <a href="{{ route('meetings.index') }}" class="menu-item {{ request()->routeIs('meetings.index') ? 'active' : '' }}">
            <i class="fa-solid fa-users-viewfinder"></i>
            <span>Meetings</span>
        </a>
        @else
        <a href="{{ route('employee.dashboard') }}" class="menu-item {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-house"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('employee.tasks') }}" class="menu-item {{ request()->routeIs('employee.tasks') ? 'active' : '' }}">
            <i class="fa-solid fa-list-check"></i>
            <span>My Tasks</span>
        </a>
        <a href="{{ route('employee.leaderboard') }}" class="menu-item {{ request()->routeIs('employee.leaderboard') ? 'active' : '' }}">
            <i class="fa-solid fa-medal"></i>
            <span>Team Leaderboard</span>
        </a>
        <a href="{{ route('employee.commits') }}" class="menu-item {{ request()->routeIs('employee.commits') ? 'active' : '' }}">
            <i class="fa-brands fa-github"></i>
            <span>GitHub Commits</span>
        </a>
        <a href="{{ route('employee.meetings') }}" class="menu-item {{ request()->routeIs('employee.meetings') ? 'active' : '' }}">
            <i class="fa-solid fa-users-viewfinder"></i>
            <span>Meetings</span>
        </a>
        <a href="{{ route('employee.attendance') }}" class="menu-item {{ request()->routeIs('employee.attendance') ? 'active' : '' }}">
            <i class="fa-solid fa-clock"></i>
            <span>My Attendance</span>
        </a>
        <a href="{{ route('employee.ai-agent.index') }}" class="menu-item {{ request()->routeIs('employee.ai-agent.*') ? 'active' : '' }}">
            <i class="fa-solid fa-robot" style="color: #6366f1;"></i>
            <span style="font-weight: 600; color: #6366f1;">AI Chatbot</span>
        </a>
        @endif
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function setupDropdown(toggleId, submenuId) {
            const toggle = document.getElementById(toggleId);
            const submenu = document.getElementById(submenuId);
            
            if (toggle && submenu) {
                if (submenu.classList.contains('show')) {
                    submenu.style.maxHeight = submenu.scrollHeight + 'px';
                }
                
                toggle.addEventListener('click', function(e) {
                    const isChevron = e.target.closest('.chevron-toggle-btn');
                    const hasRealUrl = toggle.getAttribute('href') && !toggle.getAttribute('href').startsWith('javascript:');
                    
                    if (hasRealUrl && !isChevron) {
                        return; // Let navigation proceed naturally
                    }
                    
                    e.preventDefault(); // Prevent scroll jump / navigation for toggle action
                    
                    const chevron = toggle.querySelector('.submenu-chevron');
                    if (submenu.classList.contains('show')) {
                        submenu.style.maxHeight = '0px';
                        submenu.classList.remove('show');
                        if (chevron) chevron.classList.remove('rotate-chevron');
                    } else {
                        submenu.classList.add('show');
                        submenu.style.maxHeight = submenu.scrollHeight + 'px';
                        if (chevron) chevron.classList.add('rotate-chevron');
                    }
                });
            }
        }

        setupDropdown('tasks-menu-toggle', 'tasks-submenu');
        setupDropdown('projects-menu-toggle', 'projects-submenu');
        setupDropdown('attendance-menu-toggle', 'attendance-submenu');
        setupDropdown('employees-menu-toggle', 'employees-submenu');
        setupDropdown('leaderboard-menu-toggle', 'leaderboard-submenu');
        setupDropdown('teams-menu-toggle', 'teams-submenu');
    });
</script>

