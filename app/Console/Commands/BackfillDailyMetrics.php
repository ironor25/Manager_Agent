<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackfillDailyMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backfill-daily-metrics';

    protected $description = 'Backfill historical daily metric snapshots for the leaderboard';

    public function handle()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);
        $this->info('Starting optimized chunked historical metrics backfill...');

        $startVal = microtime(true);

        // Find the earliest date across tasks, attendances, and commits
        $earliestTask = \App\Models\Task::min('created_at');
        $earliestAttendance = \App\Models\Attendance::min('date');
        $earliestCommit = \App\Models\GithubCommit::min('commit_date');

        $startDate = collect([$earliestTask, $earliestAttendance, $earliestCommit])->filter()->min();

        if (!$startDate) {
            $this->info('No historical data found.');
            return;
        }

        $startDateCarbon = \Carbon\Carbon::parse($startDate)->startOfDay();
        $endDateCarbon = now()->startOfDay();

        $this->info("Loading projects from DB...");
        $projects = \Illuminate\Support\Facades\DB::table('project_employee')
            ->select('employee_id', \Illuminate\Support\Facades\DB::raw('count(*) as project_count'))
            ->groupBy('employee_id')
            ->pluck('project_count', 'employee_id')
            ->toArray();

        $chunkStart = $startDateCarbon->copy();
        while ($chunkStart->lte($endDateCarbon)) {
            $chunkEnd = $chunkStart->copy()->addDays(30);
            if ($chunkEnd->gt($endDateCarbon)) {
                $chunkEnd = $endDateCarbon->copy();
            }

            $chunkStartStr = $chunkStart->toDateString();
            $chunkEndStr = $chunkEnd->toDateString();

            $this->info("Processing chunk: {$chunkStartStr} to {$chunkEndStr}...");

            $tasks = \Illuminate\Support\Facades\DB::table('tasks')
                ->select('employee_id', \Illuminate\Support\Facades\DB::raw('DATE(created_at) as date'),
                    \Illuminate\Support\Facades\DB::raw('count(*) as total_tasks'),
                    \Illuminate\Support\Facades\DB::raw('sum(case when completed_at is not null or status in (\'completed\', \'late_completed\') then 1 else 0 end) as completed_tasks'),
                    \Illuminate\Support\Facades\DB::raw('sum(case when (completed_at is not null or status in (\'completed\', \'late_completed\')) and (delay_hours > 0 or (deadline_at is not null and coalesce(completed_at, updated_at) > deadline_at)) then 1 else 0 end) as late_tasks')
                )
                ->whereBetween(\Illuminate\Support\Facades\DB::raw('DATE(created_at)'), [$chunkStartStr, $chunkEndStr])
                ->groupBy('employee_id', \Illuminate\Support\Facades\DB::raw('DATE(created_at)'))
                ->get();

            $attendances = \Illuminate\Support\Facades\DB::table('attendances')
                ->select('employee_id', 'date',
                    \Illuminate\Support\Facades\DB::raw('count(*) as total_attendances'),
                    \Illuminate\Support\Facades\DB::raw('sum(case when leave_flag = 0 and late_flag = 0 then 1 else 0 end) as present_days')
                )
                ->whereBetween('date', [$chunkStartStr, $chunkEndStr])
                ->groupBy('employee_id', 'date')
                ->get();

            $commits = \Illuminate\Support\Facades\DB::table('github_commits')
                ->select('employee_id', \Illuminate\Support\Facades\DB::raw('DATE(commit_date) as date'),
                    \Illuminate\Support\Facades\DB::raw('count(*) as git_commit_count')
                )
                ->whereBetween(\Illuminate\Support\Facades\DB::raw('DATE(commit_date)'), [$chunkStartStr, $chunkEndStr])
                ->groupBy('employee_id', \Illuminate\Support\Facades\DB::raw('DATE(commit_date)'))
                ->get();

            $data = [];

            foreach ($tasks as $t) {
                $key = $t->employee_id . '_' . $t->date;
                $data[$key] = [
                    'employee_id' => $t->employee_id,
                    'date' => $t->date,
                    'total_tasks' => (int)$t->total_tasks,
                    'completed_tasks' => (int)$t->completed_tasks,
                    'on_time_tasks' => (int)max(0, $t->completed_tasks - $t->late_tasks),
                    'total_attendances' => 0,
                    'present_days' => 0,
                    'git_commit_count' => 0,
                    'project_count' => $projects[$t->employee_id] ?? 0,
                ];
            }

            foreach ($attendances as $a) {
                $key = $a->employee_id . '_' . $a->date;
                if (!isset($data[$key])) {
                    $data[$key] = [
                        'employee_id' => $a->employee_id,
                        'date' => $a->date,
                        'total_tasks' => 0,
                        'completed_tasks' => 0,
                        'on_time_tasks' => 0,
                        'total_attendances' => (int)$a->total_attendances,
                        'present_days' => (int)$a->present_days,
                        'git_commit_count' => 0,
                        'project_count' => $projects[$a->employee_id] ?? 0,
                    ];
                } else {
                    $data[$key]['total_attendances'] = (int)$a->total_attendances;
                    $data[$key]['present_days'] = (int)$a->present_days;
                }
            }

            foreach ($commits as $c) {
                $key = $c->employee_id . '_' . $c->date;
                if (!isset($data[$key])) {
                    $data[$key] = [
                        'employee_id' => $c->employee_id,
                        'date' => $c->date,
                        'total_tasks' => 0,
                        'completed_tasks' => 0,
                        'on_time_tasks' => 0,
                        'total_attendances' => 0,
                        'present_days' => 0,
                        'git_commit_count' => (int)$c->git_commit_count,
                        'project_count' => $projects[$c->employee_id] ?? 0,
                    ];
                } else {
                    $data[$key]['git_commit_count'] = (int)$c->git_commit_count;
                }
            }

            $now = now()->toDateTimeString();
            $records = [];

            foreach ($data as $item) {
                $total_tasks = $item['total_tasks'];
                $completed_tasks = $item['completed_tasks'];
                $on_time_tasks = $item['on_time_tasks'];
                $total_attendances = $item['total_attendances'];
                $present_days = $item['present_days'];
                $git_commit_count = $item['git_commit_count'];

                $task_completion_score = $total_tasks > 0 ? ($completed_tasks / $total_tasks) * 100 : 0.0;
                $task_quality_score = $completed_tasks > 0 ? ($on_time_tasks / $completed_tasks) * 100 : 0.0;
                $attendance_score = $total_attendances > 0 ? ($present_days / $total_attendances) * 100 : 0.0;
                $gitlab_score = min(100.0, max(0.0, ($git_commit_count / 50) * 100));

                $overall_score = ($task_completion_score * 0.40) + ($task_quality_score * 0.20) + ($attendance_score * 0.20) + ($gitlab_score * 0.20);

                $records[] = [
                    'employee_id' => $item['employee_id'],
                    'date' => $item['date'],
                    'total_tasks' => $total_tasks,
                    'completed_tasks' => $completed_tasks,
                    'on_time_tasks' => $on_time_tasks,
                    'total_attendances' => $total_attendances,
                    'present_days' => $present_days,
                    'git_commit_count' => $git_commit_count,
                    'project_count' => $item['project_count'],
                    'task_completion_score' => round($task_completion_score, 2),
                    'task_quality_score' => round($task_quality_score, 2),
                    'attendance_score' => round($attendance_score, 2),
                    'gitlab_score' => round($gitlab_score, 2),
                    'overall_score' => round($overall_score, 2),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($records)) {
                $chunks = array_chunk($records, 1000);
                foreach ($chunks as $chunk) {
                    \Illuminate\Support\Facades\DB::table('employee_daily_metrics')->upsert(
                        $chunk,
                        ['employee_id', 'date'],
                        [
                            'total_tasks',
                            'completed_tasks',
                            'on_time_tasks',
                            'total_attendances',
                            'present_days',
                            'git_commit_count',
                            'project_count',
                            'task_completion_score',
                            'task_quality_score',
                            'attendance_score',
                            'gitlab_score',
                            'overall_score',
                            'updated_at'
                        ]
                    );
                }
            }

            unset($tasks, $attendances, $commits, $data, $records);
            gc_collect_cycles();

            $chunkStart = $chunkEnd->copy()->addDay();
        }

        $duration = round(microtime(true) - $startVal, 2);
        $this->info("Historical backfill completed in {$duration} seconds!");
    }
}
