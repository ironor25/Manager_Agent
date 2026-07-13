@extends('master')

@push('page-style')
<style>
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid var(--border-color);
        border-radius: 6px;
        padding: 4px 24px 4px 8px;
        min-width: 65px;
    }
</style>
@endpush

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1 ">Meeting Notes</h3>
        <p class="text-muted mb-0">Review qualitative meeting outcomes</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="px-4 pt-4 pb-2 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">Meeting Records</h5>
        <form action="{{ route('meetings.index') }}" method="GET" class="d-flex gap-2 align-items-center">
            @include('components.date-filter')
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive px-4 py-3">
            <table class="table table-hover align-middle w-100" id="meetings-table">
                <thead>
                    <tr>
                        <th width="200">Date</th>
                        <th width="250">Employee</th>
                        <th>Notes Summary</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($meetings as $meeting)
                    <tr>
                        <td>
                            <span class="fw-medium text-body"><i class="fa-regular fa-calendar me-2"></i>{{ $meeting->meeting_date->format('M d, Y h:i A') }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-medium text-body">{{ $meeting->employee->name ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="text-muted" style="white-space: pre-wrap;">{{ $meeting->notes_text }}</div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-5 text-muted">
                            <div class="fs-1 mb-3"><i class="fa-solid fa-users-viewfinder text-light"></i></div>
                            <h6>No meetings recorded yet.</h6>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">
                {{ $meetings->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-script')
<script>
    window.disableGlobalFilterLoader = true;
    $(document).ready(function() {
        $('#meetings-table').DataTable({
            language: {
                search: "",
                searchPlaceholder: "Search meetings..."
            },
            paging: false, // Disabling datatables paging because we use laravel pagination
            info: false,
            dom: '<"d-flex justify-content-between align-items-center mb-3"f>rt',
        });
    });
</script>
@include('components.date-filter-js')
@endpush