@extends('master')

@push('page-style')
<style>
    .hover-underline:hover {
        text-decoration: underline !important;
    }
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
        <h3 class="fw-bold mb-1 ">GitHub Commits</h3>
        <p class="text-muted mb-0">Track code contributions across the team</p>
    </div>
</div>

<div class="card border-0">
    <div class="card-body p-0">
        <!-- Search Filter Form -->
        <div class="px-4 pt-4 pb-2 border-bottom">
            <form action="{{ route('commits.index') }}" method="GET" class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="position-relative" style="width: 250px;">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted" style="padding-top: 0.375rem; padding-bottom: 0.375rem;"><i class="fa-solid fa-user"></i></span>
                            <input type="text" name="employee" id="employee-search" class="form-control border-start-0" placeholder="Filter by employee name..." value="{{ request('employee') }}" autocomplete="off">
                        </div>
                        <div id="search-suggestions" class="dropdown-menu w-100 shadow-lg border" style="display: none; position: absolute; top: 100%; left: 0; z-index: 1000; max-height: 250px; overflow-y: auto;"></div>
                    </div>
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                        <i class="fa-solid fa-magnifying-glass"></i> Filter
                    </button>
                    @if(request('employee'))
                        <a href="{{ route('commits.index') }}" class="btn btn-light border d-flex align-items-center gap-2">
                            <i class="fa-solid fa-xmark"></i> Clear
                        </a>
                    @endif
                </div>
                <div>
                    @include('components.date-filter')
                </div>
            </form>
        </div>

        <div class="table-responsive px-4 py-3">
            <table class="table table-hover align-middle w-100" id="commits-table">
                <thead>
                    <tr>
                        <th width="150">Repository</th>
                        <th>Commit Message</th>
                        <th width="120">Commit Hash</th>
                        <th>Author</th>
                        <th width="150">Date</th>
                        <th width="100" class="text-center">Link</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commits as $commit)
                    <tr>
                        <td>
                            @if($commit->url)
                                @php
                                    $repoUrl = $commit->url;
                                    if (strpos($commit->url, '/commit/') !== false) {
                                        $repoUrl = explode('/commit/', $commit->url)[0];
                                    } elseif (strpos($commit->url, '/-/commit/') !== false) {
                                        $repoUrl = explode('/-/commit/', $commit->url)[0];
                                    }
                                @endphp
                                <a href="{{ $repoUrl }}" target="_blank" class="fw-medium text-primary text-decoration-none hover-underline">
                                    <i class="fa-brands fa-{{ $commit->source === 'gitlab' ? 'gitlab' : 'github' }} me-2" @if($commit->source === 'gitlab') style="color: #fc6d26;" @endif></i>{{ $commit->repo_name }}
                                </a>
                            @else
                                <span class="fw-medium text-body">
                                    <i class="fa-brands fa-{{ $commit->source === 'gitlab' ? 'gitlab' : 'github' }} me-2" @if($commit->source === 'gitlab') style="color: #fc6d26;" @endif></i>{{ $commit->repo_name }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-body mb-1">{{ $commit->commit_message }}</div>
                        </td>
                        <td>
                            @if($commit->url)
                                <a href="{{ $commit->url }}" target="_blank" class="commit-hash text-decoration-none text-muted small" title="View Commit">
                                    {{ substr($commit->commit_hash, 0, 7) }} <i class="fa-solid fa-arrow-up-right-from-square ms-1 text-muted small" style="font-size: 0.7rem;"></i>
                                </a>
                            @else
                                <span class="commit-hash text-muted small">{{ substr($commit->commit_hash, 0, 7) }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-medium text-body">{{ $commit->employee->name ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted"><i class="fa-regular fa-calendar me-1"></i> {{ $commit->commit_date->format('M d, Y') }}</span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                @if($commit->url)
                                    <a href="{{ $commit->url }}" target="_blank" class="btn btn-sm btn-light border d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 6px;" title="Redirect to Commit History">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-secondary"></i>
                                    </a>
                                @endif
                                
                                @if($commit->project_id && $commit->commit_hash)
                                    <button class="btn btn-sm btn-light border d-inline-flex align-items-center justify-content-center text-primary view-diff-btn" data-id="{{ $commit->id }}" style="width: 32px; height: 32px; border-radius: 6px;" title="View Code Diff">
                                        <i class="fa-solid fa-code"></i>
                                    </button>
                                @endif
                                
                                @if(!$commit->url && (!$commit->project_id || !$commit->commit_hash))
                                    <span class="text-muted small">-</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
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

<!-- Code Diff Modal -->
<div class="modal fade" id="diffModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <h5 class="modal-title fw-bold ">
            <i class="fa-solid fa-code-compare text-primary me-2"></i> Code Diff
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="diffModalBody">
          <!-- Diff Content Injected Here -->
      </div>
    </div>
  </div>
</div>
@endsection

@push('page-script')
<script>
    window.disableGlobalFilterLoader = true;
    $(document).ready(function() {
        // Initialize DataTable only if there are actual commit rows (avoid error on empty state colspan)
        if ($('#commits-table tbody tr td[colspan]').length === 0) {
            $('#commits-table').DataTable({
                language: {
                    search: "",
                    searchPlaceholder: "Search commits..."
                },
                paging: false, // Disabling datatables paging because we use laravel pagination
                info: false,
                dom: '<"d-flex justify-content-between align-items-center mb-3"f>rt',
            });
        }

        // Autocomplete search suggestions
        let searchInput = $('#employee-search');
        let suggestionsContainer = $('#search-suggestions');
        let searchForm = searchInput.closest('form');

        searchInput.on('input', function() {
            let query = $(this).val().trim();
            if (query.length < 2) {
                suggestionsContainer.hide().empty();
                return;
            }

            $.ajax({
                url: "{{ route('employees.search') }}",
                type: 'GET',
                data: { q: query },
                success: function(data) {
                    suggestionsContainer.empty();
                    if (data && data.length > 0) {
                        data.forEach(function(employee) {
                            let item = $('<a class="dropdown-item py-2" href="#"></a>');
                            item.html(`<div class="fw-bold text-body">${employee.name}</div><small class="text-muted">${employee.team}</small>`);
                            item.on('click', function(e) {
                                e.preventDefault();
                                searchInput.val(employee.name);
                                suggestionsContainer.hide();
                                searchForm.submit();
                            });
                            suggestionsContainer.append(item);
                        });
                        suggestionsContainer.show();
                    } else {
                        suggestionsContainer.hide();
                    }
                },
                error: function() {
                    suggestionsContainer.hide();
                }
            });
        });

        // Hide suggestions when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#employee-search, #search-suggestions').length) {
                suggestionsContainer.hide();
            }
        });

        // Handle Diff Button Click
        $('.view-diff-btn').on('click', function() {
            let commitId = $(this).data('id');
            let diffModal = new bootstrap.Modal(document.getElementById('diffModal'));
            let modalBody = $('#diffModalBody');
            
            modalBody.html('<div class="text-center py-5"><div class="spinner-border text-primary mb-3" role="status"></div><h6 class="">Fetching diff from GitLab...</h6></div>');
            diffModal.show();

            $.ajax({
                url: `/admin/commits/${commitId}/diff`,
                type: 'GET',
                success: function(data) {
                    if (!data || data.length === 0) {
                        modalBody.html('<div class="alert alert-info">No diff available for this commit.</div>');
                        return;
                    }
                    
                    let html = '';
                    data.forEach(function(file) {
                        let fileName = file.new_path === file.old_path ? file.new_path : `${file.old_path} &rarr; ${file.new_path}`;
                        html += `
                            <div class="card mb-3 border-0 shadow-sm">
                                <div class="card-header bg-light border-bottom-0 py-2 d-flex align-items-center">
                                    <i class="fa-regular fa-file-code me-2 text-muted"></i>
                                    <span class="fw-bold text-body" style="font-family: monospace;">${fileName}</span>
                                </div>
                                <div class="card-body p-0">
                                    <pre class="m-0 p-3" style="background-color: #1e1e1e; color: #d4d4d4; font-size: 0.85rem; overflow-x: auto;"><code style="font-family: 'Consolas', 'Courier New', monospace;">`;
                        
                        if (file.diff) {
                            let lines = file.diff.split('\n');
                            lines.forEach(function(line) {
                                if (line.startsWith('+')) {
                                    html += `<span style="color: #4ade80; display: block; background-color: rgba(74, 222, 128, 0.1); width: 100%;">${line.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</span>`;
                                } else if (line.startsWith('-')) {
                                    html += `<span style="color: #f87171; display: block; background-color: rgba(248, 113, 113, 0.1); width: 100%;">${line.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</span>`;
                                } else if (line.startsWith('@@')) {
                                    html += `<span style="color: #60a5fa; display: block; margin-top: 4px; margin-bottom: 4px;">${line.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</span>`;
                                } else {
                                    html += `<span style="display: block;">${line.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</span>`;
                                }
                            });
                        } else {
                            html += `<span>File changed but no diff provided.</span>`;
                        }
                        
                        html += `</code></pre>
                                </div>
                            </div>
                        `;
                    });
                    
                    modalBody.html(html);
                },
                error: function(xhr) {
                    let errMsg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Failed to load diff.';
                    modalBody.html(`<div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i> ${errMsg}</div>`);
                }
            });
        });
    });
</script>
@include('components.date-filter-js')
@endpush