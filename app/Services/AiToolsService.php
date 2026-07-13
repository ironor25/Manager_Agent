<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;

class AiToolsService
{
    /**
     * Get the JSON schema array of available tools for Ollama.
     */
    public static function getToolsDefinition(string $role = 'admin'): array
    {
        if ($role === 'employee') {
            return [
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'get_my_profile',
                        'description' => 'Get my own employee profile details (e.g. name, designation, team, status, join date).',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [],
                        ],
                    ]
                ],
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'get_my_tasks',
                        'description' => 'Get the list of tasks assigned to me, including title, description, status, priority, and deadlines.',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'status' => [
                                    'type' => 'string',
                                    'description' => 'Optional task status filter (pending, in_progress, completed, late_completed).',
                                ],
                            ],
                        ],
                    ]
                ],
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'get_my_attendance',
                        'description' => 'Get my attendance logs and summary.',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'month' => [
                                    'type' => 'integer',
                                    'description' => 'Optional month filter (1-12). Default is current month.',
                                ],
                                'year' => [
                                    'type' => 'integer',
                                    'description' => 'Optional year filter. Default is current year.',
                                ],
                            ],
                        ],
                    ]
                ],
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'get_my_performance',
                        'description' => 'Get my performance scores and rank in my team.',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [],
                        ],
                    ]
                ],
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'get_my_commits',
                        'description' => 'Get my GitHub commit history, including messages and commit dates.',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'limit' => [
                                    'type' => 'integer',
                                    'description' => 'The number of recent commits to return. Default is 5.',
                                ],
                            ],
                        ],
                    ]
                ]
            ];
        }

        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_top_employees',
                    'description' => 'Get the top performing employees in the organization based on their overall score.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'limit' => [
                                'type' => 'integer',
                                'description' => 'The number of top employees to return. Default is 5.',
                            ],
                            'team' => [
                                'type' => 'string',
                                'description' => 'Optional team name to filter by (e.g., Engineering, Sales, Marketing, Legal).',
                            ],
                        ],
                    ],
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_top_teams',
                    'description' => 'Get the top performing teams in the organization.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'limit' => [
                                'type' => 'integer',
                                'description' => 'The number of top teams to return. Default is 5.',
                            ],
                        ],
                    ],
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_team_details',
                    'description' => 'Get performance details for a specific team.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'team_name' => [
                                'type' => 'string',
                                'description' => 'The name of the team to search for (e.g., Engineering, Marketing, Sales).',
                            ],
                        ],
                        'required' => ['team_name'],
                    ],
                ]
            ]
        ];
    }

    /**
     * Dispatch the tool call to the appropriate method.
     */
    public static function handleToolCall(string $name, array $arguments)
    {
        $user = Auth::user();
        $employee = $user->employee;

        switch ($name) {
            // Admin Tools
            case 'get_top_employees':
                if ($user->role !== 'admin') return ['error' => 'Unauthorized tool call.'];
                return self::getTopEmployees(
                    $arguments['limit'] ?? 5,
                    $arguments['team'] ?? null
                );
            case 'get_top_teams':
                if ($user->role !== 'admin') return ['error' => 'Unauthorized tool call.'];
                return self::getTopTeams($arguments['limit'] ?? 5);
            case 'get_team_details':
                if ($user->role !== 'admin') return ['error' => 'Unauthorized tool call.'];
                return self::getTeamDetails($arguments['team_name'] ?? '');

            // Employee Tools
            case 'get_my_profile':
                if (!$employee) return ['error' => 'No employee profile associated with this user.'];
                return [
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'team' => $employee->team,
                    'designation' => $employee->designation,
                    'status' => $employee->status,
                    'join_date' => $employee->join_date,
                ];
            case 'get_my_tasks':
                if (!$employee) return ['error' => 'No employee profile associated with this user.'];
                $query = $employee->tasks();
                if (!empty($arguments['status'])) {
                    $query->where('status', $arguments['status']);
                }
                return $query->get(['title', 'description', 'status', 'priority', 'deadline_at'])->toArray();
            case 'get_my_attendance':
                if (!$employee) return ['error' => 'No employee profile associated with this user.'];
                $month = $arguments['month'] ?? now()->month;
                $year = $arguments['year'] ?? now()->year;
                $attendances = $employee->attendances()
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->get();
                return $attendances->map(function($att) {
                    return [
                        'date' => $att->date ? $att->date->format('Y-m-d') : null,
                        'status' => $att->leave_flag ? 'leave' : ($att->late_flag ? 'late' : 'present'),
                        'login_time' => $att->login_time ? $att->login_time->format('H:i') : null,
                        'logout_time' => $att->logout_time ? $att->logout_time->format('H:i') : null,
                    ];
                })->toArray();
            case 'get_my_performance':
                if (!$employee) return ['error' => 'No employee profile associated with this user.'];
                $overallMetric = DB::table('employee_period_metrics')->where('employee_id', $employee->id)->where('period', 'all_time')->first();
                return [
                    'overall_score' => $overallMetric ? $overallMetric->overall_score : null,
                    'task_completion_score' => $overallMetric ? $overallMetric->task_completion_score : null,
                    'attendance_score' => $overallMetric ? $overallMetric->attendance_score : null,
                    'gitlab_score' => $overallMetric ? $overallMetric->gitlab_score : null,
                ];
            case 'get_my_commits':
                if (!$employee) return ['error' => 'No employee profile associated with this user.'];
                $limit = $arguments['limit'] ?? 5;
                return $employee->github_commits()
                    ->orderBy('commit_date', 'desc')
                    ->limit($limit)
                    ->get(['commit_hash', 'commit_message', 'commit_date'])
                    ->toArray();

            default:
                return ['error' => 'Tool not found.'];
        }
    }

    private static function getTopEmployees(int $limit = 5, ?string $team = null)
    {
        $query = DB::table('employee_performance_metrics')
            ->join('employees', 'employees.id', '=', 'employee_performance_metrics.employee_id')
            ->select('employees.name', 'employees.team', 'employee_performance_metrics.final_score as score')
            ->orderByDesc('score');

        if ($team) {
            $query->where('employees.team', 'like', "%{$team}%");
        }

        $topEmployees = $query->limit($limit)->get();

        return $topEmployees->toArray();
    }

    private static function getTopTeams(int $limit = 5)
    {
        $topTeams = DB::table('team_performance_metrics')
            ->where('period', 'all')
            ->select('team_name as name', DB::raw('(task_completion_score * 0.4 + leadership_score * 0.3 + attendance_score * 0.3) as score'))
            ->orderByDesc('score')
            ->limit($limit)
            ->get();

        return $topTeams->toArray();
    }

    private static function getTeamDetails(string $teamName)
    {
        $teamMetric = DB::table('team_performance_metrics')
            ->where('period', 'all')
            ->where('team_name', 'like', "%{$teamName}%")
            ->first();

        if (!$teamMetric) {
            return ['error' => "Team '{$teamName}' not found or no metrics available."];
        }

        return (array) $teamMetric;
    }
}
