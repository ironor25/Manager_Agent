@extends('master')

@push('page-style')
<style>
    .calendar-container {
        background: var(--surface);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-md);
        padding: 24px;
    }
    .calendar-header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }
    .month-nav-btn {
        background: var(--surface-hover);
        border: 1px solid var(--border-color);
        color: var(--text-main);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 0.2s, color 0.2s;
    }
    .month-nav-btn:hover {
        background-color: var(--primary);
        color: white;
        border-color: var(--primary);
    }
    .current-month-display {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-main);
        min-width: 200px;
        text-align: center;
    }
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 16px;
    }
    .calendar-day-header {
        text-align: center;
        font-weight: 700;
        padding: 10px 0;
        text-transform: uppercase;
        font-size: 0.85rem;
        color: var(--text-muted);
        border-bottom: 2px solid var(--border-color);
    }
    .calendar-day-cell {
        min-height: 120px;
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-md);
        padding: 14px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.2s, box-shadow 0.2s;
        position: relative;
    }
    .calendar-day-cell:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
    }
    .calendar-day-cell.other-month {
        opacity: 0.3;
        background: transparent;
        border-style: dashed;
        pointer-events: none;
    }
    .day-num {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-main);
    }
    .status-badge {
        font-size: 0.75rem;
        padding: 4px 8px;
        border-radius: 12px;
        font-weight: 600;
        display: inline-block;
        margin-top: 6px;
    }
    .cell-present {
        border-color: #10b981;
        background: rgba(16, 185, 129, 0.03);
    }
    .cell-present .status-badge {
        background-color: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }
    .cell-late {
        border-color: #f59e0b;
        background: rgba(245, 158, 11, 0.03);
    }
    .cell-late .status-badge {
        background-color: rgba(245, 158, 11, 0.1);
        color: #d97706;
    }
    .cell-leave {
        border-color: #8b5cf6;
        background: rgba(139, 92, 246, 0.03);
    }
    .cell-leave .status-badge {
        background-color: rgba(139, 92, 246, 0.1);
        color: #8b5cf6;
    }
    .cell-absent {
        border-color: #ef4444;
        background: rgba(239, 68, 68, 0.03);
    }
    .cell-absent .status-badge {
        background-color: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }
    .cell-weekend {
        border-color: var(--border-color);
        background: var(--surface-hover);
        opacity: 0.85;
    }
    .cell-weekend .status-badge {
        background-color: rgba(100, 116, 139, 0.1);
        color: var(--text-muted);
    }
    .cell-future {
        border-color: var(--border-color);
        background: var(--surface);
    }
    .time-info {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: 8px;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
</style>
@endpush

@section('page-content')
<div class="container-fluid">
    @if(!$employee)
        <div class="alert alert-warning border-0 shadow-sm p-4 mb-4" role="alert">
            <h5 class="alert-heading fw-bold mb-1">No Profile Linked</h5>
            <p class="mb-0 text-muted">Your account is not linked to any employee profile.</p>
        </div>
    @else
        <div class="calendar-container">
            <!-- Header Filter Bar -->
            <div class="calendar-header-bar">
                <div class="d-flex align-items-center gap-2">
                    <button class="month-nav-btn" id="prevMonthBtn" title="Previous Month">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <div class="current-month-display" id="monthYearTitle">May 2026</div>
                    <button class="month-nav-btn" id="nextMonthBtn" title="Next Month">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
                
                <div class="d-flex align-items-center gap-3">
                    <select class="form-select form-select-sm" id="monthFilter" style="width: 130px;">
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                    
                    <select class="form-select form-select-sm" id="yearFilter" style="width: 100px;">
                        @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <!-- Calendar Display -->
            <div class="calendar-grid" id="calendarGrid">
                <!-- Day Headers -->
                <div class="calendar-day-header">Monday</div>
                <div class="calendar-day-header">Tuesday</div>
                <div class="calendar-day-header">Wednesday</div>
                <div class="calendar-day-header">Thursday</div>
                <div class="calendar-day-header">Friday</div>
                <div class="calendar-day-header">Saturday</div>
                <div class="calendar-day-header">Sunday</div>

                <!-- Days cells will be appended here dynamically -->
            </div>
        </div>
    @endif
</div>
@endsection

@push('page-script')
<script>
$(document).ready(function() {
    let currentMonth = new Date().getMonth() + 1; // 1-12
    let currentYear = new Date().getFullYear();

    // Set initial select filters
    $('#monthFilter').val(currentMonth);
    $('#yearFilter').val(currentYear);

    // Initialize calendar
    fetchAndRenderCalendar(currentMonth, currentYear);

    // Event listeners
    $('#monthFilter, #yearFilter').change(function() {
        currentMonth = parseInt($('#monthFilter').val());
        currentYear = parseInt($('#yearFilter').val());
        fetchAndRenderCalendar(currentMonth, currentYear);
    });

    $('#prevMonthBtn').click(function() {
        currentMonth--;
        if (currentMonth < 1) {
            currentMonth = 12;
            currentYear--;
        }
        $('#monthFilter').val(currentMonth);
        $('#yearFilter').val(currentYear);
        fetchAndRenderCalendar(currentMonth, currentYear);
    });

    $('#nextMonthBtn').click(function() {
        currentMonth++;
        if (currentMonth > 12) {
            currentMonth = 1;
            currentYear++;
        }
        $('#monthFilter').val(currentMonth);
        $('#yearFilter').val(currentYear);
        fetchAndRenderCalendar(currentMonth, currentYear);
    });

    function fetchAndRenderCalendar(month, year) {
        // Update Title Display
        const monthNames = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];
        $('#monthYearTitle').text(monthNames[month - 1] + ' ' + year);

        // Fetch attendance logs
        $.ajax({
            url: "{{ route('employee.attendance.data') }}",
            type: "GET",
            data: { month: month, year: year },
            success: function(records) {
                renderCalendarGrid(month, year, records);
            },
            error: function() {
                alert("Failed to load attendance logs. Please try again.");
            }
        });
    }

    function renderCalendarGrid(month, year, records) {
        // Map records by YYYY-MM-DD
        const recordMap = {};
        records.forEach(function(rec) {
            if (rec.date) {
                // Handle carbon or string format
                const dateKey = rec.date.split('T')[0];
                recordMap[dateKey] = rec;
            }
        });

        const grid = $('#calendarGrid');
        // Clear all except headers
        grid.find('.calendar-day-cell').remove();

        const firstDay = new Date(year, month - 1, 1);
        let startDay = firstDay.getDay() - 1; // Mon = 0, Sun = 6
        if (startDay < 0) startDay = 6; // Sunday fix

        const daysInMonth = new Date(year, month, 0).getDate();
        
        // Add placeholders for prior month days
        for (let i = 0; i < startDay; i++) {
            grid.append('<div class="calendar-day-cell other-month"></div>');
        }

        const todayStr = getLocalDateString(new Date());

        // Add month days
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const cellDate = new Date(year, month - 1, day);
            const dayOfWeek = cellDate.getDay(); // 0 = Sun, 6 = Sat
            const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
            const isFuture = dateStr > todayStr;

            let cellClass = "cell-future";
            let badgeText = "";
            let timeInfoHtml = "";

            if (isFuture) {
                cellClass = "cell-future";
                badgeText = "Scheduled";
            } else {
                const rec = recordMap[dateStr];
                if (rec) {
                    if (rec.status === 'leave') {
                        cellClass = "cell-leave";
                        badgeText = "On Leave";
                    } else if (rec.status === 'late') {
                        cellClass = "cell-late";
                        badgeText = "Late";
                        const inTime = rec.login_time || 'N/A';
                        const outTime = rec.logout_time || 'N/A';
                        timeInfoHtml = `
                            <div class="time-info">
                                <div><i class="fa-solid fa-right-to-bracket text-warning me-1"></i>In: ${inTime}</div>
                                <div><i class="fa-solid fa-right-from-bracket text-muted me-1"></i>Out: ${outTime}</div>
                            </div>`;
                    } else {
                        cellClass = "cell-present";
                        badgeText = "Present";
                        const inTime = rec.login_time || 'N/A';
                        const outTime = rec.logout_time || 'N/A';
                        timeInfoHtml = `
                            <div class="time-info">
                                <div><i class="fa-solid fa-right-to-bracket text-success me-1"></i>In: ${inTime}</div>
                                <div><i class="fa-solid fa-right-from-bracket text-muted me-1"></i>Out: ${outTime}</div>
                            </div>`;
                    }
                } else {
                    if (isWeekend) {
                        cellClass = "cell-weekend";
                        badgeText = "Weekend";
                    } else {
                        cellClass = "cell-absent";
                        badgeText = "Absent";
                    }
                }
            }

            const cellHtml = `
                <div class="calendar-day-cell ${cellClass}">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="day-num">${day}</span>
                        <span class="status-badge">${badgeText}</span>
                    </div>
                    ${timeInfoHtml}
                </div>`;
            grid.append(cellHtml);
        }
    }

    function getLocalDateString(date) {
        const offset = date.getTimezoneOffset();
        const localDate = new Date(date.getTime() - (offset*60*1000));
        return localDate.toISOString().split('T')[0];
    }
});
</script>
@endpush
