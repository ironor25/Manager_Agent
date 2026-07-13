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
                <h4 class="fw-bold mb-0"><i class="fa-solid fa-users-viewfinder text-info me-2"></i>My Meetings</h4>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100 mb-0" id="employee-meetings-table">
                        <thead class="table-light">
                            <tr>
                                <th>Meeting Note Summary</th>
                                <th width="200">Date & Time</th>
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
<script>
$(document).ready(function() {
    $('#employee-meetings-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('employee.meetings.data') }}",
        columns: [
            { data: 'notes_summary', name: 'notes_text' },
            { data: 'meeting_date', name: 'meeting_date', class: 'small' }
        ]
    });
});
</script>
@endpush
