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
    public function generateReport(int $employeeId){
         try {
             // Check if a recent report exists within the last 7 days
             $existingReport = Performance_report::where('employee_id', $employeeId)
                 ->where('created_at', '>=', now()->subWeek())
                 ->orderBy('created_at', 'desc')
                 ->first();

             if ($existingReport) {
                 $analytics = PerformanceAnalyticsService::calculateEmployeeScore($employeeId);
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
             $analytics = PerformanceAnalyticsService::calculateEmployeeScore($employeeId);
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
            $employees = \App\Models\Employee::all();
            $generatedCount = 0;
            $skippedCount = 0;

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

                $analytics = PerformanceAnalyticsService::calculateEmployeeScore($employeeId);
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
