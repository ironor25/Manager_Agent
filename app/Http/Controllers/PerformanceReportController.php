<?php

namespace App\Http\Controllers;

use App\Models\Performance_report;
use App\Services\AgentService;
use App\Services\GeminiService;
use App\Services\PerformanceAnalyticsService;
use Illuminate\Http\Request;

class PerformanceReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected function getEmployeeAnalytics(int $employeeId)
    {
        $precomputed = \App\Models\EmployeePerformanceMetric::with('employee')->where('employee_id', $employeeId)->first();

        if ($precomputed) {
            $meetingNotes = \App\Models\MeetingNote::where('employee_id', $employeeId)
                ->latest('meeting_date')
                ->take(15)
                ->pluck('notes_text')
                ->toArray();

            return [
                'employee_id' => $precomputed->employee_id,
                'employee_name' => $precomputed->employee->name ?? 'Unknown',
                'task_completion_rate' => $precomputed->task_completion_rate,
                'on_time_completion_rate' => $precomputed->on_time_completion_rate,
                'attendance_score' => $precomputed->attendance_score,
                'git_contribution_score' => $precomputed->git_contribution_score,
                'final_leadership_score' => $precomputed->final_score,
                'total_tasks' => $precomputed->total_tasks,
                'completed_tasks' => $precomputed->completed_tasks,
                'late_tasks' => $precomputed->late_tasks,
                'total_attendance_records' => $precomputed->total_attendance_records,
                'git_commit_count' => $precomputed->git_commit_count,
                'recent_commits' => $precomputed->recent_commits ?? [],
                'commit_chart_data' => $precomputed->commit_chart_data ?? [],
                'meeting_notes' => $meetingNotes,
            ];
        }

        return PerformanceAnalyticsService::calculateEmployeeScore($employeeId);
    }

    /**
     * Display a listing of the resource.
     */
    public function generateReport(int $employeeId){
         try {
             // Check if a recent report exists within the last 7 days
             $existingReport = Performance_report::where('employee_id', $employeeId)
                 ->where('created_at', '>=', now()->subWeek())
                 ->orderBy('created_at', 'desc')
                 ->first();

             if ($existingReport) {
                 $analytics = $this->getEmployeeAnalytics($employeeId);
                 $report = [
                     'summary' => $existingReport->summary,
                     'strengths' => $existingReport->strengths ?? [],
                     'weaknesses' => $existingReport->weaknesses ?? [],
                     'recommendations' => $existingReport->recommendations ?? [],
                     'leadership_score' => $existingReport->leadership_score ?? 0,
                     'chart_data' => $analytics['commit_chart_data'] ?? [],
                 ];
                 return response()->json($report);
             }

             // No valid cached report found, generate a new one
             $analytics = $this->getEmployeeAnalytics($employeeId);
             $prompt = AgentService::fixedPrompt($analytics);
             
             /** @var string|array|null $rawReport */
             $rawReport = AgentService::generateSummary($prompt);
             
             // Ensure it is an array or object
             $rawReport = str_replace(['```json', '```'], '', $rawReport);

             $report = json_decode(trim($rawReport), true);
            
             // Save the new report into the database
             if (is_array($report)) {
                 Performance_report::create([
                     'employee_id' => $employeeId,
                     'leadership_score' => $report['leadership_score'] ?? 0,
                     'summary' => $report['summary'] ?? '',
                     'strengths' => $report['strengths'] ?? [],
                     'weaknesses' => $report['weaknesses'] ?? [],
                     'recommendations' => $report['recommendations'] ?? [],
                 ]);
             }
             
             if (is_array($report)) {
                 $report['chart_data'] = $analytics['commit_chart_data'] ?? [];
             }

             return response()->json($report);
         } catch (\Exception $e) {
             return response()->json(['error' => $e->getMessage()], 500);
         }
    } 

    public function generateAllReports()
    {
        // Prevent timeout during report generation
        set_time_limit(0);

        try {
            $generatedCount = 0;
            $skippedCount = 0;

            \App\Models\Employee::chunk(200, function ($employees) use (&$generatedCount, &$skippedCount) {
                foreach ($employees as $employee) {
                    $employeeId = $employee->id;
                    
                    // Check if a recent report exists within the last 7 days
                    $existingReport = Performance_report::where('employee_id', $employeeId)
                        ->where('created_at', '>=', now()->subWeek())
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($existingReport) {
                        $skippedCount++;
                        continue;
                    }

                    $analytics = $this->getEmployeeAnalytics($employeeId);
                    $prompt = AgentService::fixedPrompt($analytics);
                    
                    /** @var string|array|null $rawReport */
                    $rawReport = AgentService::generateSummary($prompt);
                    
                    $rawReport = str_replace(['```json', '```'], '', $rawReport);
                    $report = json_decode(trim($rawReport), true);
                
                    if (is_array($report)) {
                        Performance_report::create([
                            'employee_id' => $employeeId,
                            'leadership_score' => $report['leadership_score'] ?? 0,
                            'summary' => $report['summary'] ?? '',
                            'strengths' => $report['strengths'] ?? [],
                            'weaknesses' => $report['weaknesses'] ?? [],
                            'recommendations' => $report['recommendations'] ?? [],
                        ]);
                        $generatedCount++;
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'all employees report is generated'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Performance_report $performance_report)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Performance_report $performance_report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Performance_report $performance_report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Performance_report $performance_report)
    {
        //
    }
}
