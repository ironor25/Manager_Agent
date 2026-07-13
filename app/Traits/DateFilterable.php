<?php

namespace App\Traits;

use Carbon\Carbon;

trait DateFilterable
{
    /**
     * Get start and end date based on filter
     */
    protected function getDateRange($filter, $startDate = null, $endDate = null)
    {
        $now = Carbon::now();
        switch ($filter) {
            case 'daily':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
            case 'weekly':
                return [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()];
            case 'monthly':
                return [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()];
            case 'quarterly':
                return [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()];
            case 'yearly':
                return [$now->copy()->startOfYear(), $now->copy()->endOfYear()];
            case 'custom':
                if ($startDate && $endDate) {
                    return [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()];
                }
                break;
        }
        return [null, null]; // all time
    }
}
