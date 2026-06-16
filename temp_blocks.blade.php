<!-- Team Management Section (to be inserted after Leaderboard row) -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-body">Team Management</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTeamModal">
                    <i class="fa-solid fa-plus me-1"></i> Add New Team
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3">Team Name</th>
                                <th class="py-3">Description</th>
                                <th class="py-3">Members</th>
                                <th class="text-end px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($teamModels as $team)
                            <tr>
                                <td class="px-4">
                                    <div class="fw-bold text-body">{{ $team->name }}</div>
                                </td>
                                <td>
                                    <div class="text-muted small text-truncate" style="max-width: 300px;">
                                        {{ $team->description ?? 'No description provided.' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-primary border border-primary-subtle">
                                        <i class="fa-solid fa-users me-1"></i> {{ $team->employees->count() }}
                                    </span>
                                </td>
                                <td class="text-end px-4">
                                    <button class="action-btn manage-members-btn" data-team="{{ $team->name }}" data-members="{{ json_encode($team->employees->map(function($e){return ['id'=>$e->id, 'name'=>$e->name];})) }}" title="Manage Members">
                                        <i class="fa-solid fa-users-gear"></i>
                                    </button>
                                    <button class="action-btn edit-team-btn" data-team="{{ $team->name }}" data-desc="{{ $team->description }}" title="Edit Team">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button class="action-btn delete-btn trigger-team-delete" data-team="{{ $team->name }}" title="Delete Team">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals to be inserted before @endsection -->
<!-- Create Team Modal -->
<div class="modal fade" id="createTeamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('teams.store') }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-body"><i class="fa-solid fa-plus-circle text-primary me-2"></i>Create New Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-medium">Team Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Engineering">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Optional team details..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Create Team</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Team Modal -->
<div class="modal fade" id="editTeamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="editTeamForm" method="POST" class="modal-content border-0 shadow">
            @csrf
            @method('PUT')
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-body"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-medium">Team Name</label>
                    <input type="text" id="edit_team_name" class="form-control" disabled>
                    <small class="text-muted">Team names cannot be changed currently.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Description</label>
                    <textarea name="description" id="edit_team_description" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Manage Members Modal -->
<div class="modal fade" id="manageMembersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-body"><i class="fa-solid fa-users-gear text-primary me-2"></i>Manage Members: <span id="manageMembersTeamName" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <!-- Current Members -->
                    <div class="col-md-6 border-end">
                        <h6 class="fw-bold mb-3">Current Members</h6>
                        <ul class="list-group list-group-flush" id="currentMembersList">
                            <!-- Populated via JS -->
                        </ul>
                    </div>
                    <!-- Add Member Form -->
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Add Member</h6>
                        <form id="addMemberForm" method="POST">
                            @csrf
                            <div class="d-flex gap-2">
                                <select name="employee_id" class="form-select" required>
                                    <option value="">Select Employee</option>
                                    @foreach($allEmployees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->team ?? 'No Team' }})</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary">Add</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Team Modal -->
<div class="modal fade" id="deleteTeamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-4 border-0 shadow">
            <div class="mb-3">
                <i class="fa-solid fa-triangle-exclamation text-danger" style="font-size: 3rem;"></i>
            </div>
            <h5 class="mb-3 fw-bold">Delete Team</h5>
            <p class="text-muted mb-4" style="font-size: 0.9rem;">Are you sure you want to delete this team? Members will be safely unassigned.</p>
            <div class="d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteTeamForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
