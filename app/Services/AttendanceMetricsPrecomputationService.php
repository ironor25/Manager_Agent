<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\AttendanceMetric;
use App\Models\AttendanceMetricsTrend;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceMetricsPrecomputationService
{
    public function calculateAllMetrics()
    {
        Log::info("Starting attendance metrics precomputation...");
        $startTime = microtime(true);

        $this->calculateEmployeeLevelMetrics();
        $this->calculateTrendMetrics();

        $duration = microtime(true) - $startTime;
        Log::info("Attendance metrics precomputation completed in {$duration} seconds.");
    }

    private function calculateEmployeeLevelMetrics()
    {
        $now = Carbon::now();

        // Let's assume 22 expected days for testing/simplicity or calculate from DB.
        // We'll calculate total expected days as 22 if not provided.
        $expectedDays = 22;

        $metricsQuery = DB::table('attendances as a')
            ->select(
                'a.employee_id',
                DB::raw('COUNT(a.id) as present_days'),
                DB::raw("SUM(CASE WHEN a.late_flag = 1 THEN 1 ELSE 0 END) as late_days"),
                DB::raw("SUM(CASE WHEN a.leave_flag = 1 THEN 1 ELSE 0 END) as leave_days")
            )
            ->groupBy('a.employee_id');

        $metricsData = [];

        foreach ($metricsQuery->cursor() as $row) {
            $absentDays = max(0, $expectedDays - $row->present_days - $row->leave_days);
            $attendancePercentage = ($row->present_days / max(1, $expectedDays)) * 100;
            $attendancePercentage = min(100, $attendancePercentage); // Cap at 100

            $punctualityScore = 100.0;
            if ($row->present_days > 0) {
                $punctualityScore = max(0, 100 - (($row->late_days / $row->present_days) * 100));
            }

            $leaveScore = 100.0;
            if ($row->leave_days > 2) { // arbitrary rule: > 2 leaves drops score
                $leaveScore = max(0, 100 - (($row->leave_days - 2) * 10));
            }

            // Attendance Score = (0.7 * Attendance %) + (0.2 * Punctuality Score) + (0.1 * Leave Score)
            $attendanceScore = (0.7 * $attendancePercentage) + (0.2 * $punctualityScore) + (0.1 * $leaveScore);
            $attendanceScore = max(0, min(100, $attendanceScore));

            $category = 'Needs Attention';
            if ($attendanceScore >= 90) $category = 'Excellent';
            elseif ($attendanceScore >= 75) $category = 'Good';
            elseif ($attendanceScore >= 60) $category = 'Average';

            $metricsData[] = [
                'employee_id' => $row->employee_id,
                'total_expected_days' => $expectedDays,
                'present_days' => $row->present_days,
                'absent_days' => $absentDays,
                'late_days' => $row->late_days,
                'leave_days' => $row->leave_days,
                'attendance_percentage' => round($attendancePercentage, 2),
                'punctuality_score' => round($punctualityScore, 2),
                'leave_utilization_score' => round($leaveScore, 2),
                'attendance_score' => round($attendanceScore, 2),
                'attendance_category' => $category,
                'updated_at' => $now,
                'created_at' => $now,
            ];
        }

        // Upsert in chunks to handle many employees
        $chunks = array_chunk($metricsData, 500);
        foreach ($chunks as $chunk) {
            AttendanceMetric::upsert(
                $chunk,
                ['employee_id'],
                [
                    'total_expected_days', 
                    'present_days', 
                    'absent_days', 
                    'late_days', 
                    'leave_days', 
                    'attendance_percentage', 
                    'punctuality_score', 
                    'leave_utilization_score', 
                    'attendance_score', 
                    'attendance_category', 
                    'updated_at'
                ]
            );
        }
    }

    private function calculateTrendMetrics()
    {
        $today = Carbon::today()->toDateString();
        
        $presentCount = Attendance::where('date', $today)->where('leave_flag', false)->count();
        $lateCount = Attendance::where('date', $today)->where('late_flag', true)->count();
        $leaveCount = Attendance::where('date', $today)->where('leave_flag', true)->count();
        $totalEmployees = Employee::count();
        $absentCount = max(0, $totalEmployees - $presentCount - $leaveCount);

        $avgAttendance = 0;
        if ($totalEmployees > 0) {
            $avgAttendance = ($presentCount / $totalEmployees) * 100;
        }

        AttendanceMetricsTrend::updateOrCreate(
            ['date' => $today],
            [
                'present_count' => $presentCount,
                'absent_count' => $absentCount,
                'late_count' => $lateCount,
                'leave_count' => $leaveCount,
                'avg_attendance_percentage' => round($avgAttendance, 2),
            ]
        );
    }
}
