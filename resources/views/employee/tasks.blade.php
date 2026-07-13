@extends('master')

@section('page-content')
<div class="container-fluid">
    @if(!$employee)
        <div class="alert alert-warning border-0 shadow-sm p-4 mb-4" role="alert">
            <h5 class="alert-heading fw-bold mb-1">No Profile Linked</h5>
            <p class="mb-0 text-muted">Your account is not linked to any employee profile.</p>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                <h4 class="fw-bold mb-0"><i class="fa-solid fa-list-check text-primary me-2"></i>My Tasks</h4>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100 mb-0" id="employee-tasks-table">
                        <thead class="table-light">
                            <tr>
                                <th>Task Title</th>
                                <th>Description</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Time Spent</th>
                                <th>Actions</th>
                                <th>Assigned At</th>
                                <th>Deadline</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('page-script')
<style>
.dataTables_processing.silent-processing {
    display: none !important;
}
</style>
<script>
// Offset tracking for server/client desynchronization
const serverTimeOffset = Math.floor(Date.now() / 1000) - {{ time() }};

function formatTime(totalSeconds) {
    const h = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
    const m = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
    const s = String(totalSeconds % 60).padStart(2, '0');
    return `${h}:${m}:${s}`;
}

function updateTimers() {
    const serverNow = Math.floor(Date.now() / 1000) - serverTimeOffset;
    $('.timer-display').each(function() {
        const $this = $(this);
        const accumulatedSeconds = parseInt($this.data('accumulated-seconds')) || 0;
        const startedAtUnix = $this.data('timer-started-at');

        if (startedAtUnix) {
            const elapsedSeconds = serverNow - parseInt(startedAtUnix);
            const totalSeconds = Math.max(0, accumulatedSeconds + elapsedSeconds);
            $this.text(formatTime(totalSeconds));
            $this.addClass('text-success fw-bold');
        } else {
            $this.text(formatTime(accumulatedSeconds));
            $this.removeClass('text-success fw-bold');
        }
    });
}

// Tick timers every second
setInterval(updateTimers, 1000);

$(document).ready(function() {
    const table = $('#employee-tasks-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('employee.tasks.data') }}",
        columns: [
            { data: 'title', name: 'title', class: 'fw-semibold text-body' },
            { data: 'description', name: 'description', defaultContent: '<span class="text-muted small">No description.</span>' },
            { data: 'priority_badge', name: 'priority' },
            { data: 'status_badge', name: 'status' },
            { data: 'time_spent', name: 'time_spent', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'deadline_at', name: 'deadline_at', class: 'fw-semibold text-danger' }
        ],
        drawCallback: function() {
            // Recalculate timers immediately on redraw
            updateTimers();
        }
    });

    // AJAX Action Helpers
    function ajaxTimerRequest(url) {
        $.ajax({
            url: url,
            type: 'POST',
            success: function(response) {
                if (response.success) {
                    $('.dataTables_processing').addClass('silent-processing');
                    table.ajax.reload(function() {
                        $('.dataTables_processing').removeClass('silent-processing');
                    }, false);
                }
            },
            error: function(xhr) {
                const err = xhr.responseJSON?.error || 'An error occurred.';
                alert(err);
                // On error, reload to revert optimistic UI
                table.ajax.reload(null, false);
            }
        });
    }

    $(document).on('click', '.start-timer-btn', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const id = $btn.data('id');
        const $row = $btn.closest('tr');
        
        // Optimistic UI Update
        $btn.addClass('d-none');
        $row.find('.pause-timer-btn').removeClass('d-none');
        
        const serverNow = Math.floor(Date.now() / 1000) - serverTimeOffset;
        $row.find('.timer-display').attr('data-timer-started-at', serverNow).data('timer-started-at', serverNow);
        $row.find('td:eq(3)').html('<span class="badge bg-primary">In progress</span>');
        
        updateTimers(); // Force instant tick
        
        ajaxTimerRequest(`/employee/tasks/${id}/start`);
    });

    $(document).on('click', '.pause-timer-btn', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const id = $btn.data('id');
        const $row = $btn.closest('tr');
        
        // Optimistic UI Update
        $btn.addClass('d-none');
        $row.find('.start-timer-btn').removeClass('d-none');
        
        const $timer = $row.find('.timer-display');
        const startedAt = parseInt($timer.attr('data-timer-started-at') || $timer.data('timer-started-at'));
        if (startedAt) {
            const serverNow = Math.floor(Date.now() / 1000) - serverTimeOffset;
            const elapsed = serverNow - startedAt;
            const accumulated = parseInt($timer.attr('data-accumulated-seconds') || $timer.data('accumulated-seconds')) || 0;
            $timer.attr('data-accumulated-seconds', accumulated + elapsed).data('accumulated-seconds', accumulated + elapsed);
            $timer.removeAttr('data-timer-started-at').removeData('timer-started-at');
        }
        
        updateTimers(); // Force instant tick
        
        ajaxTimerRequest(`/employee/tasks/${id}/pause`);
    });

    $(document).on('click', '.stop-timer-btn', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const id = $btn.data('id');
        const $row = $btn.closest('tr');
        
        if (!confirm('Are you sure you want to stop the timer and mark this task as completed?')) return;
        
        // Optimistic UI Update
        $row.find('td:eq(5)').html('<span class="text-muted small">No Actions</span>');
        $row.find('td:eq(3)').html('<span class="badge bg-success">Completed</span>');
        
        const $timer = $row.find('.timer-display');
        const startedAt = parseInt($timer.attr('data-timer-started-at') || $timer.data('timer-started-at'));
        if (startedAt) {
            const serverNow = Math.floor(Date.now() / 1000) - serverTimeOffset;
            const elapsed = serverNow - startedAt;
            const accumulated = parseInt($timer.attr('data-accumulated-seconds') || $timer.data('accumulated-seconds')) || 0;
            $timer.attr('data-accumulated-seconds', accumulated + elapsed).data('accumulated-seconds', accumulated + elapsed);
            $timer.removeAttr('data-timer-started-at').removeData('timer-started-at');
        }
        
        updateTimers(); // Force instant tick
        
        ajaxTimerRequest(`/employee/tasks/${id}/stop`);
    });
});
</script>
@endpush
