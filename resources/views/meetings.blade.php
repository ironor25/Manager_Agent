@extends('master')

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-body">Meeting Notes</h3>
        <p class="text-muted mb-0">Review qualitative meeting outcomes</p>
    </div>
</div>

<div class="card border-0">
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
                                <div class="avatar bg-light text-primary fw-bold" style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                                    {{ substr($meeting->employee->name ?? '?', 0, 1) }}
                                </div>
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
@endpush
