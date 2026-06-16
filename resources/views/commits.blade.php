@extends('master')

@push('page-style')
 
@endpush

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-body">GitHub Commits</h3>
        <p class="text-muted mb-0">Track code contributions across the team</p>
    </div>
</div>

<div class="card border-0">
    <div class="card-body p-0">
        <div class="table-responsive px-4 py-3">
            <table class="table table-hover align-middle w-100" id="commits-table">
                <thead>
                    <tr>
                        <th width="150">Repository</th>
                        <th>Commit Message</th>
                        <th>Author</th>
                        <th width="150">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commits as $commit)
                    <tr>
                        <td>
                            <span class="fw-medium text-body"><i class="fa-brands fa-github me-2"></i>{{ $commit->repo_name }}</span>
                        </td>
                        <td>
                            <div class="fw-bold text-body mb-1">{{ $commit->commit_message }}</div>
                            <span class="commit-hash">{{ substr($commit->commit_hash, 0, 7) }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar bg-light text-primary fw-bold" style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                                    {{ substr($commit->employee->name ?? '?', 0, 1) }}
                                </div>
                                <span class="fw-medium text-body">{{ $commit->employee->name ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted"><i class="fa-regular fa-calendar me-1"></i> {{ $commit->commit_date->format('M d, Y') }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <div class="fs-1 mb-3"><i class="fa-brands fa-git-alt text-light"></i></div>
                            <h6>No commits recorded yet.</h6>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">
                {{ $commits->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-script')
<script>
    $(document).ready(function() {
        $('#commits-table').DataTable({
            language: {
                search: "",
                searchPlaceholder: "Search commits..."
            },
            paging: false, // Disabling datatables paging because we use laravel pagination
            info: false,
            dom: '<"d-flex justify-content-between align-items-center mb-3"f>rt',
        });
    });
</script>
@endpush
