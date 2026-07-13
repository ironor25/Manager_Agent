@extends('master')

@section('page-content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0  fw-bold">Developer Tools</h1>
            <p class="text-muted mb-0">Manage API Keys and integrate external systems.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <!-- API Keys Management -->
        <div class="col-lg-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-key text-primary me-2"></i> API Keys</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#generateKeyModal">
                        <i class="fa-solid fa-plus me-1"></i> Generate API Key
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Key Name</th>
                                    <th>API Key</th>
                                    <th>Created Date</th>
                                    <th>Last Used</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($apiKeys as $key)
                                <tr>
                                    <td class="fw-medium">{{ $key->name }}</td>
                                    <td>
                                        <div class="input-group input-group-sm" style="max-width: 280px;">
                                            <input type="password" class="form-control" value="{{ $key->api_key }}" id="key-{{ $key->id }}" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="toggleVisibility('key-{{ $key->id }}')" title="Toggle Visibility">
                                                <i class="fa-regular fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{{ $key->api_key }}')" title="Copy to Clipboard">
                                                <i class="fa-regular fa-copy"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td>{{ $key->created_at->format('M d, Y') }}</td>
                                    <td>{{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'Never' }}</td>
                                    <td>
                                        @if($key->is_active)
                                            <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 border border-success border-opacity-25 rounded-pill">Active</span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 border border-danger border-opacity-25 rounded-pill">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <form action="{{ route('api-keys.toggle', $key->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light border me-1" title="{{ $key->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="fa-solid {{ $key->is_active ? 'fa-ban text-warning' : 'fa-check text-success' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('api-keys.destroy', $key->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this API Key?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light border text-danger" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-key fs-3 mb-3 d-block text-secondary"></i>
                                        No API keys generated yet. Click "Generate API Key" to get started.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Documentation -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-book text-primary me-2"></i> API Documentation</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-4" id="apiTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-medium" id="curl-tab" data-bs-toggle="tab" data-bs-target="#curl" type="button" role="tab">CURL</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-medium" id="php-tab" data-bs-toggle="tab" data-bs-target="#php" type="button" role="tab">PHP</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-medium" id="node-tab" data-bs-toggle="tab" data-bs-target="#node" type="button" role="tab">NodeJS</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-medium" id="python-tab" data-bs-toggle="tab" data-bs-target="#python" type="button" role="tab">Python</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="apiTabsContent">
                        <!-- CURL Tab -->
                        <div class="tab-pane fade show active" id="curl" role="tabpanel">
                            <h6 class="fw-bold">Example: Push Task Data</h6>
                            <p class="text-muted small mb-2"><code>POST /api/tasks</code></p>
                            <pre class="bg-dark text-white p-3 rounded mb-4" style="font-size: 0.85rem;"><code>curl -X POST {{ url('/api/tasks') }} \
-H "Authorization: Bearer YOUR_API_KEY" \
-H "Content-Type: application/json" \
-d '{
    "title": "Fix login bug",
    "description": "Resolve the issue on the mobile login screen."
}'</code></pre>
                        </div>

                        <!-- PHP Tab -->
                        <div class="tab-pane fade" id="php" role="tabpanel">
                            <h6 class="fw-bold">Example: Push Task Data (Laravel HTTP)</h6>
                            <p class="text-muted small mb-2"><code>POST /api/tasks</code></p>
                            <pre class="bg-dark text-white p-3 rounded mb-4" style="font-size: 0.85rem;"><code>use Illuminate\Support\Facades\Http;

$response = Http::withToken('YOUR_API_KEY')->post('{{ url('/api/tasks') }}', [
    'title' => 'Fix login bug',
    'description' => 'Resolve the issue on the mobile login screen.'
]);

$data = $response->json();</code></pre>
                        </div>

                        <!-- NodeJS Tab -->
                        <div class="tab-pane fade" id="node" role="tabpanel">
                            <h6 class="fw-bold">Example: Fetch Employees Data (Axios)</h6>
                            <p class="text-muted small mb-2"><code>GET /api/employees</code></p>
                            <pre class="bg-dark text-white p-3 rounded mb-4" style="font-size: 0.85rem;"><code>const axios = require('axios');

axios.get('{{ url('/api/employees') }}', {
  headers: {
    'Authorization': `Bearer YOUR_API_KEY`
  }
})
.then(response => {
  console.log(response.data);
})
.catch(error => {
  console.error('API Error:', error);
});</code></pre>
                        </div>

                        <!-- Python Tab -->
                        <div class="tab-pane fade" id="python" role="tabpanel">
                            <h6 class="fw-bold">Example: Fetch Leaderboard Data (Requests)</h6>
                            <p class="text-muted small mb-2"><code>GET /api/leaderboard</code></p>
                            <pre class="bg-dark text-white p-3 rounded mb-4" style="font-size: 0.85rem;"><code>import requests

url = "{{ url('/api/leaderboard') }}"
headers = {
    "Authorization": "Bearer YOUR_API_KEY"
}

response = requests.get(url, headers=headers)
print(response.json())</code></pre>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    <h6 class="fw-bold mb-3">Available Endpoints</h6>
                    <div class="table-responsive">
                        <table class="table table-sm border align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Method</th>
                                    <th>Endpoint</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody style="font-size: 0.9rem;">
                                <tr data-bs-toggle="collapse" data-bs-target="#payload-get-employees" aria-expanded="false" style="cursor: pointer;">
                                    <td><span class="badge bg-primary">GET</span></td>
                                    <td><code>/api/employees</code></td>
                                    <td class="text-muted d-flex justify-content-between align-items-center">List all employees <i class="fa-solid fa-chevron-down"></i></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="p-0 border-0">
                                        <div class="collapse" id="payload-get-employees" style="transition: height 0.4s ease-in-out;">
                                            <div class="bg-light p-3 m-0">
                                        <strong>Response Structure:</strong>
                                        <pre class="bg-dark text-white p-2 rounded mt-2 mb-0" style="font-size: 0.8rem;"><code>{
  "data": [
    {
      "id": 1,
      "name": "Jane Doe",
      "email": "jane@example.com",
      "team": "Engineering",
      "created_at": "2023-10-01T10:00:00.000000Z",
      "updated_at": "2023-10-01T10:00:00.000000Z"
    }
  ]
}</code></pre>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr data-bs-toggle="collapse" data-bs-target="#payload-get-leaderboard" aria-expanded="false" style="cursor: pointer;">
                                    <td><span class="badge bg-primary">GET</span></td>
                                    <td><code>/api/leaderboard</code></td>
                                    <td class="text-muted d-flex justify-content-between align-items-center">Get team/employee leaderboard <i class="fa-solid fa-chevron-down"></i></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="p-0 border-0">
                                        <div class="collapse" id="payload-get-leaderboard" style="transition: height 0.4s ease-in-out;">
                                            <div class="bg-light p-3 m-0">
                                        <strong>Response Structure:</strong>
                                        <pre class="bg-dark text-white p-2 rounded mt-2 mb-0" style="font-size: 0.8rem;"><code>{
  "data": [
    {
      "employee_id": 1,
      "name": "Jane Doe",
      "score": 95.5
    }
  ]
}</code></pre>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr data-bs-toggle="collapse" data-bs-target="#payload-get-teams" aria-expanded="false" style="cursor: pointer;">
                                    <td><span class="badge bg-primary">GET</span></td>
                                    <td><code>/api/teams</code></td>
                                    <td class="text-muted d-flex justify-content-between align-items-center">List all teams <i class="fa-solid fa-chevron-down"></i></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="p-0 border-0">
                                        <div class="collapse" id="payload-get-teams" style="transition: height 0.4s ease-in-out;">
                                            <div class="bg-light p-3 m-0">
                                        <strong>Response Structure:</strong>
                                        <pre class="bg-dark text-white p-2 rounded mt-2 mb-0" style="font-size: 0.8rem;"><code>{
  "data": [
    {
      "name": "Engineering"
    }
  ]
}</code></pre>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr data-bs-toggle="collapse" data-bs-target="#payload-get-team-report" aria-expanded="false" style="cursor: pointer;">
                                    <td><span class="badge bg-primary">GET</span></td>
                                    <td><code>/api/team/{id}/report</code></td>
                                    <td class="text-muted d-flex justify-content-between align-items-center">Get performance report for a team <i class="fa-solid fa-chevron-down"></i></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="p-0 border-0">
                                        <div class="collapse" id="payload-get-team-report" style="transition: height 0.4s ease-in-out;">
                                            <div class="bg-light p-3 m-0">
                                        <strong>Response Structure:</strong>
                                        <pre class="bg-dark text-white p-2 rounded mt-2 mb-0" style="font-size: 0.8rem;"><code>{
  "data": {
    "id": 1,
    "team_name": "Engineering",
    "efficiency_score": 88.5,
    "top_performer_id": 1,
    "top_performer_name": "Jane Doe",
    "summary": "Excellent performance...",
    "strengths": ["Communication", "Delivery"],
    "weaknesses": ["Testing coverage"],
    "recommendations": ["Improve testing"],
    "created_at": "2023-10-01T10:00:00.000000Z",
    "updated_at": "2023-10-01T10:00:00.000000Z"
  },
  "status": "success"
}</code></pre>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr data-bs-toggle="collapse" data-bs-target="#payload-get-employee-report" aria-expanded="false" style="cursor: pointer;">
                                    <td><span class="badge bg-primary">GET</span></td>
                                    <td><code>/api/employee/{id}/report</code></td>
                                    <td class="text-muted d-flex justify-content-between align-items-center">Get performance report for an employee <i class="fa-solid fa-chevron-down"></i></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="p-0 border-0">
                                        <div class="collapse" id="payload-get-employee-report" style="transition: height 0.4s ease-in-out;">
                                            <div class="bg-light p-3 m-0">
                                        <strong>Response Structure:</strong>
                                        <pre class="bg-dark text-white p-2 rounded mt-2 mb-0" style="font-size: 0.8rem;"><code>{
  "data": {
    "id": 1,
    "employee_id": 1,
    "leadership_score": 92.0,
    "summary": "Great leader...",
    "strengths": ["Mentorship", "Vision"],
    "weaknesses": ["Time management"],
    "recommendations": ["Delegate more"],
    "created_at": "2023-10-01T10:00:00.000000Z",
    "updated_at": "2023-10-01T10:00:00.000000Z",
    "employee": {
      "id": 1,
      "name": "Jane Doe",
      "email": "jane@example.com",
      "team": "Engineering",
      "created_at": "2023-10-01T10:00:00.000000Z",
      "updated_at": "2023-10-01T10:00:00.000000Z"
    }
  },
  "status": "success"
}</code></pre>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr data-bs-toggle="collapse" data-bs-target="#payload-post-tasks" aria-expanded="false" style="cursor: pointer;">
                                    <td><span class="badge bg-success">POST</span></td>
                                    <td><code>/api/tasks</code></td>
                                    <td class="text-muted d-flex justify-content-between align-items-center">Ingest new task (External System integration) <i class="fa-solid fa-chevron-down"></i></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="p-0 border-0">
                                        <div class="collapse" id="payload-post-tasks" style="transition: height 0.4s ease-in-out;">
                                            <div class="bg-light p-3 m-0">
                                        <strong>Request Payload (JSON):</strong>
                                        <pre class="bg-dark text-white p-2 rounded mt-2 mb-0" style="font-size: 0.8rem;"><code>{
  "employee_id": 1,
  "title": "Fix login issue",
  "description": "Optional description of task",
  "status": "Pending",
  "assigned_at": "2023-10-01 09:00:00",
  "started_at": "2023-10-01 10:00:00",
  "deadline_at": "2023-10-05 17:00:00",
  "completed_at": null,
  "estimated_hours": 10.5,
  "actual_hours": null,
  "delay_hours": null,
  "priority": "High"
}</code></pre>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr data-bs-toggle="collapse" data-bs-target="#payload-put-tasks-progress" aria-expanded="false" style="cursor: pointer;">
                                    <td><span class="badge bg-warning text-dark">PUT</span></td>
                                    <td><code>/api/tasks/{id}/progress</code></td>
                                    <td class="text-muted d-flex justify-content-between align-items-center">Update task progress (started/completed) <i class="fa-solid fa-chevron-down"></i></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="p-0 border-0">
                                        <div class="collapse" id="payload-put-tasks-progress" style="transition: height 0.4s ease-in-out;">
                                            <div class="bg-light p-3 m-0">
                                        <strong>Request Payload (JSON):</strong>
                                        <pre class="bg-dark text-white p-2 rounded mt-2 mb-0" style="font-size: 0.8rem;"><code>{
  "started_at": "2023-10-01 09:00:00",
  "completed_at": "2023-10-05 17:00:00"
}</code></pre>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr data-bs-toggle="collapse" data-bs-target="#payload-post-attendance" aria-expanded="false" style="cursor: pointer;">
                                    <td><span class="badge bg-success">POST</span></td>
                                    <td><code>/api/attendance</code></td>
                                    <td class="text-muted d-flex justify-content-between align-items-center">Ingest attendance data (HR Integration) <i class="fa-solid fa-chevron-down"></i></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="p-0 border-0">
                                        <div class="collapse" id="payload-post-attendance" style="transition: height 0.4s ease-in-out;">
                                            <div class="bg-light p-3 m-0">
                                        <strong>Request Payload (JSON):</strong>
                                        <pre class="bg-dark text-white p-2 rounded mt-2 mb-0" style="font-size: 0.8rem;"><code>{
  "employee_id": 1,
  "login_time": "2023-10-01 08:55:00",
  "logout_time": "2023-10-01 17:15:00",
  "late_flag": false,
  "leave_flag": false,
  "date": "2023-10-01"
}</code></pre>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr data-bs-toggle="collapse" data-bs-target="#payload-post-employees" aria-expanded="false" style="cursor: pointer;">
                                    <td><span class="badge bg-success">POST</span></td>
                                    <td><code>/api/employees</code></td>
                                    <td class="text-muted d-flex justify-content-between align-items-center">Ingest new employee (HR Integration) <i class="fa-solid fa-chevron-down"></i></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="p-0 border-0">
                                        <div class="collapse" id="payload-post-employees" style="transition: height 0.4s ease-in-out;">
                                            <div class="bg-light p-3 m-0">
                                        <strong>Request Payload (JSON):</strong>
                                        <pre class="bg-dark text-white p-2 rounded mt-2 mb-0" style="font-size: 0.8rem;"><code>{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "team": "Engineering"
}</code></pre>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Tester -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-flask text-primary me-2"></i> API Tester</h5>
                </div>
                <div class="card-body">
                    <form id="apiTesterForm" onsubmit="event.preventDefault(); testApi();">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Select API Key</label>
                            <select class="form-select" id="testApiKey" required>
                                <option value="">-- Choose Key --</option>
                                @foreach($apiKeys->where('is_active', true) as $key)
                                    <option value="{{ $key->api_key }}">{{ $key->name }}</option>
                                @endforeach
                            </select>
                            @if($apiKeys->where('is_active', true)->isEmpty())
                                <div class="form-text text-danger"><i class="fa-solid fa-circle-exclamation"></i> You need an active API Key first.</div>
                            @endif
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">Endpoint</label>
                            <select class="form-select" id="testEndpoint" required>
                                <option value="/api/leaderboard">GET /api/leaderboard</option>
                                <option value="/api/teams">GET /api/teams</option>
                                <option value="/api/employees">GET /api/employees</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-medium" id="testBtn" {{ $apiKeys->where('is_active', true)->isEmpty() ? 'disabled' : '' }}>
                            <i class="fa-solid fa-paper-plane me-1"></i> Send Request
                        </button>
                    </form>

                    <div class="mt-4 d-none" id="responseContainer">
                        <h6 class="fw-bold  small mb-2">Response JSON</h6>
                        <pre class="p-3 rounded border" id="responseOutput" style="max-height: 250px; overflow-y: auto; font-size: 0.85rem; margin-bottom: 0;"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate Key Modal -->
<div class="modal fade" id="generateKeyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Generate New API Key</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('api-keys.store') }}" method="POST">
                @csrf
                <div class="modal-body pb-0">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-medium small">Key Name / Identifier</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Workdesk Integration" required>
                        <div class="form-text">Give your key a descriptive name so you remember its purpose.</div>
                    </div>
                    <div class="alert alert-warning border-warning border-opacity-25 bg-warning bg-opacity-10 p-3 mb-4 rounded" style="font-size: 0.9rem;">
                        <i class="fa-solid fa-triangle-exclamation text-warning me-2"></i><strong>Security Note:</strong> Keep your keys secure. Do not share them in publicly accessible repositories.
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Key</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('page-script')
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            // Optional: you could trigger a bootstrap toast here if desired
            alert('API Key copied to clipboard!');
        }, function(err) {
            console.error('Could not copy text: ', err);
        });
    }

    function toggleVisibility(inputId) {
        const input = document.getElementById(inputId);
        const icon = input.nextElementSibling.querySelector('i');
        
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    async function testApi() {
        const btn = document.getElementById('testBtn');
        const output = document.getElementById('responseOutput');
        const container = document.getElementById('responseContainer');
        const apiKey = document.getElementById('testApiKey').value;
        const endpoint = document.getElementById('testEndpoint').value;

        if(!apiKey) return;

        btn.disabled = true;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Sending...';
        
        try {
            const response = await fetch(endpoint, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${apiKey}`,
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            output.textContent = JSON.stringify(data, null, 2);
            
            if (response.ok) {
                output.className = 'bg-light p-3 rounded border text-body';
            } else {
                output.className = 'bg-danger bg-opacity-10 p-3 rounded border border-danger text-danger';
            }
        } catch (error) {
            output.textContent = 'Error: ' + error.message;
            output.className = 'bg-danger bg-opacity-10 p-3 rounded border border-danger text-danger';
        } finally {
            container.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
</script>
@endpush
