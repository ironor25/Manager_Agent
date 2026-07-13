<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Employee;
use App\Models\Team;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\DateFilterable;

class ProjectController extends Controller
{
    use DateFilterable;

    /**
     * Display the Project Management page.
     */
    public function index()
    {
        $employees = Employee::where('designation', 'Manager')->select('id', 'name')->get();
        $teams = Team::select('name')->get();
        return view('projects.management', compact('employees', 'teams'));
    }

    /**
     * Return JSON data of projects for Yajra DataTables (Management View).
     */
    public function getProjects(Request $request)
    {
        if ($request->ajax()) {
            $data = Project::with(['manager', 'team'])->withCount('tasks');
            return \Yajra\DataTables\Facades\DataTables::of($data)
                ->addColumn('manager_name', function($row) {
                    return $row->manager ? $row->manager->name : 'Unassigned';
                })
                ->addColumn('team_name_display', function($row) {
                    return $row->team_name ?: 'None';
                })
                ->addColumn('status_badge', function($row) {
                    $colors = [
                        'active' => 'primary',
                        'completed' => 'success',
                        'on_hold' => 'warning',
                        'archived' => 'secondary'
                    ];
                    $color = $colors[$row->status] ?? 'secondary';
                    return '<span class="badge bg-'.$color.'">'.ucfirst(str_replace('_', ' ', $row->status)).'</span>';
                })
                ->addColumn('action', function($row) {
                    $projectJson = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                    return '
                        <div class="d-flex justify-content-end align-items-center gap-2">
                            <a href="'.route('projects.show', $row->id).'" class="action-btn" title="View Members">
                                <i class="fa-solid fa-users text-info"></i>
                            </a>
                            <button class="action-btn edit-project-btn" data-project="'.$projectJson.'" title="Edit Project">
                                <i class="fa-solid fa-pen text-primary"></i>
                            </button>
                            <button class="action-btn delete-btn delete-project-btn" data-action="'.route('projects.destroy', $row->id).'" title="Delete Project">
                                <i class="fa-solid fa-trash text-danger"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:projects,name',
            'description' => 'nullable|string',
            'status' => 'required|string|in:active,completed,on_hold,archived',
            'category' => 'nullable|string|max:255',
            'team_name' => 'nullable|string|exists:teams,name',
            'manager_id' => 'nullable|integer|exists:employees,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget_hours' => 'nullable|numeric|min:0',
            'repo_name' => 'nullable|string|max:255',
            'repo_url' => 'nullable|url|max:255',
            'requirements' => 'nullable|string',
        ]);

        Project::create($request->all());

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }

    /**
     * Display specific project (usually for members management).
     */
    public function show(Project $project)
    {
        $employees = Employee::all();
        return view('projects.show', compact('project', 'employees'));
    }

    /**
     * Return JSON data of project members for Yajra DataTables.
     */
    public function getMembersData(Request $request, Project $project)
    {
        if ($request->ajax()) {
            $data = $project->employees()->select(['employees.id', 'employees.name', 'employees.email', 'employees.team']);
            return \Yajra\DataTables\Facades\DataTables::of($data)
                ->addColumn('action', function($row) use ($project) {
                    return '
                        <form action="'.route('projects.members.remove', [$project->id, $row->id]).'" method="POST" class="d-inline remove-member-form">
                            '.csrf_field().'
                            '.method_field('DELETE').'
                            <button type="submit" class="btn btn-sm btn-light text-danger border-0">
                                <i class="fa-solid fa-user-minus me-1"></i> Remove
                            </button>
                        </form>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    /**
     * Return JSON data of project tasks for Yajra DataTables.
     */
    public function getTasksData(Request $request, Project $project)
    {
        if ($request->ajax()) {
            $data = $project->tasks()->with('employee');
            return \Yajra\DataTables\Facades\DataTables::of($data)
                ->addColumn('employee_name', function($row) {
                    return $row->employee ? $row->employee->name : 'Unassigned';
                })
                ->addColumn('status_badge', function($row) {
                    $colors = [
                        'pending' => 'secondary',
                        'in_progress' => 'primary',
                        'in_review' => 'info',
                        'completed' => 'success',
                        'late_completed' => 'warning'
                    ];
                    $color = $colors[$row->status] ?? 'secondary';
                    return '<span class="badge bg-'.$color.'">'.ucfirst(str_replace('_', ' ', $row->status)).'</span>';
                })
                ->rawColumns(['status_badge'])
                ->make(true);
        }
    }

    /**
     * Return JSON data of project commits for Yajra DataTables.
     */
    public function getCommitsData(Request $request, Project $project)
    {
        if ($request->ajax()) {
            if (!$project->repo_name) {
                return \Yajra\DataTables\Facades\DataTables::of(collect([]))->make(true);
            }
            
            // Fetch github commits
            $githubCommits = \App\Models\GithubCommit::with('employee')
                ->where('repo_name', $project->repo_name)
                ->get()
                ->map(function($commit) {
                    $commit->source = 'github';
                    return $commit;
                });
                
            $allCommits = $githubCommits->sortByDesc('commit_date');

            return \Yajra\DataTables\Facades\DataTables::of($allCommits)
                ->addColumn('employee_name', function($row) {
                    return $row->employee ? $row->employee->name : 'Unknown';
                })
                ->addColumn('source_badge', function($row) {
                    if ($row->source === 'github') {
                        return '<span class="badge bg-dark"><i class="fa-brands fa-github me-1"></i>GitHub</span>';
                    } else {
                        return '<span class="badge bg-warning text-dark"><i class="fa-brands fa-gitlab me-1"></i>GitLab</span>';
                    }
                })
                ->addColumn('commit_date_formatted', function($row) {
                    return $row->commit_date ? \Carbon\Carbon::parse($row->commit_date)->format('Y-m-d H:i') : '';
                })
                ->rawColumns(['source_badge'])
                ->make(true);
        }
    }

    /**
     * Update the specified project in storage.
     */
    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:projects,name,' . $project->id,
            'description' => 'nullable|string',
            'status' => 'required|string|in:active,completed,on_hold,archived',
            'category' => 'nullable|string|max:255',
            'team_name' => 'nullable|string|exists:teams,name',
            'manager_id' => 'nullable|integer|exists:employees,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget_hours' => 'nullable|numeric|min:0',
            'repo_name' => 'nullable|string|max:255',
            'repo_url' => 'nullable|url|max:255',
            'requirements' => 'nullable|string',
        ]);

        $project->update($request->all());

        // Sync is_archived with status
        if ($project->status === 'archived') {
            $project->update(['is_archived' => true]);
        } else {
            $project->update(['is_archived' => false]);
        }

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }

    /**
     * Add a member to the project.
     */
    public function addMember(Request $request, Project $project)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $project->employees()->syncWithoutDetaching([$request->employee_id]);
        return redirect()->route('projects.show', $project->id)->with('success', 'Member added to project successfully.');
    }

    /**
     * Remove a member from the project.
     */
    public function removeMember(Project $project, $employeeId)
    {
        $project->employees()->detach($employeeId);
        return redirect()->route('projects.show', $project->id)->with('success', 'Member removed from project successfully.');
    }

    /**
     * Display the Project Monitoring page.
     */
    public function monitoring()
    {
        return view('projects.monitoring');
    }

    /**
     * Return JSON data for Project Monitoring dashboard.
     */
    public function getMonitoringData(Request $request)
    {
        $filter = $request->get('date_filter', 'all_time');
        [$start, $end] = $this->getDateRange($filter, $request->get('start_date'), $request->get('end_date'));

        $projectsQuery = Project::query();
        if ($filter !== 'all_time') {
            $projectsQuery->whereBetween('created_at', [$start, $end]);
        }

        $projectIds = $projectsQuery->pluck('id');

        // Calculate KPIs from ProjectMetric table
        $metrics = \App\Models\ProjectMetric::with('project')->whereIn('project_id', $projectIds)->get();
        
        $activeProjectsCount = Project::whereIn('id', $projectIds)->where('is_archived', false)->where('status', '!=', 'completed')->count();
        $atRiskCount = $metrics->where('health_status', 'At Risk')->count();
        $criticalCount = $metrics->where('health_status', 'Critical')->count();
        
        $avgCompletion = $metrics->count() > 0 ? $metrics->avg('completion_percentage') : 0;

        // Status Chart Data
        $statusCounts = Project::whereIn('id', $projectIds)->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')->toArray();
            
        // Category Chart Data
        $categoryCounts = Project::whereIn('id', $projectIds)->select('category', DB::raw('count(*) as count'))
            ->whereNotNull('category')
            ->groupBy('category')
            ->pluck('count', 'category')->toArray();
            
        // Health Chart Data
        $healthCounts = \App\Models\ProjectMetric::whereIn('project_id', $projectIds)->select('health_status', DB::raw('count(*) as count'))
            ->groupBy('health_status')
            ->pluck('count', 'health_status')->toArray();

        return response()->json([
            'kpis' => [
                'active_projects' => $activeProjectsCount,
                'at_risk' => $atRiskCount,
                'critical' => $criticalCount,
                'avg_completion' => round($avgCompletion, 1),
            ],
            'charts' => [
                'status' => $statusCounts,
                'categories' => $categoryCounts,
                'health' => $healthCounts,
            ]
        ]);
    }
    
    /**
     * Return JSON data for monitoring table.
     */
    public function getMonitoringTable(Request $request)
    {
        if ($request->ajax()) {
            $filter = $request->get('date_filter', 'all_time');
            [$start, $end] = $this->getDateRange($filter, $request->get('start_date'), $request->get('end_date'));

            $data = \App\Models\ProjectMetric::with(['project', 'project.manager'])->select('project_metrics.*');
            
            if ($filter !== 'all_time') {
                $data->whereHas('project', function($q) use ($start, $end) {
                    $q->whereBetween('created_at', [$start, $end]);
                });
            }

            return \Yajra\DataTables\Facades\DataTables::of($data)
                ->addColumn('project_name', function($row) {
                    return $row->project->name ?? 'Unknown';
                })
                ->addColumn('manager', function($row) {
                    return $row->project && $row->project->manager ? $row->project->manager->name : 'Unassigned';
                })
                ->addColumn('progress_bar', function($row) {
                    $val = min(100, max(0, $row->completion_percentage));
                    $color = 'success';
                    if ($val < 50) $color = 'danger';
                    elseif ($val < 80) $color = 'warning';
                    
                    return '
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height: 6px;">
                                <div class="progress-bar bg-'.$color.'" role="progressbar" style="width: '.$val.'%"></div>
                            </div>
                            <span class="small text-muted">'.round($val).'%</span>
                        </div>
                    ';
                })
                ->addColumn('health_badge', function($row) {
                    $colors = [
                        'On Track' => 'success',
                        'At Risk' => 'warning',
                        'Critical' => 'danger'
                    ];
                    $color = $colors[$row->health_status] ?? 'secondary';
                    return '<span class="badge bg-'.$color.'">'.$row->health_status.'</span>';
                })
                ->rawColumns(['progress_bar', 'health_badge'])
                ->make(true);
        }
    }

    /**
     * Display the Project Reports page.
     */
    public function reports()
    {
        return view('projects.reports');
    }

    /**
     * Return JSON data for Project Reports table.
     */
    public function getReportsData(Request $request)
    {
        if ($request->ajax()) {
            $filter = $request->get('date_filter', 'all_time');
            [$start, $end] = $this->getDateRange($filter, $request->get('start_date'), $request->get('end_date'));

            $data = \App\Models\ProjectMetric::with(['project', 'project.team'])->select('project_metrics.*');
            
            if ($filter !== 'all_time') {
                $data->whereHas('project', function($q) use ($start, $end) {
                    $q->whereBetween('created_at', [$start, $end]);
                });
            }

            return \Yajra\DataTables\Facades\DataTables::of($data)
                ->addColumn('project_name', function($row) {
                    return $row->project->name ?? 'Unknown';
                })
                ->addColumn('team', function($row) {
                    return $row->project && $row->project->team_name ? $row->project->team_name : 'None';
                })
                ->addColumn('budget_utilization', function($row) {
                    $budget = $row->project->budget_hours ?? 0;
                    $actual = $row->total_actual_hours;
                    
                    if ($budget > 0) {
                        $percent = round(($actual / $budget) * 100);
                        $color = $percent > 100 ? 'text-danger' : 'text-success';
                        return "<span class='$color'>{$actual} / {$budget} hrs ({$percent}%)</span>";
                    }
                    return "<span>{$actual} hrs (No budget)</span>";
                })
                ->rawColumns(['budget_utilization'])
                ->make(true);
        }
    }
}
