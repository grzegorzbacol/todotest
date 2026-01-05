<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * ASSUMPTION: WeekService calculates week boundaries starting from Monday
 * TODO: Consider timezone handling for production
 */
class WeekService
{
    /**
     * Get week start (Monday) and end (Sunday) for a given date
     */
    public function getWeekBounds(?string $startDate = null): array
    {
        $date = $startDate ? Carbon::parse($startDate) : Carbon::now();
        
        // Get Monday of the week
        $weekStart = $date->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        return [
            'weekStart' => $weekStart->format('Y-m-d'),
            'weekEnd' => $weekEnd->format('Y-m-d'),
        ];
    }

    /**
     * Get array of 7 days (Monday to Sunday) with dates
     */
    public function getWeekDays(?string $startDate = null): array
    {
        $date = $startDate ? Carbon::parse($startDate) : Carbon::now();
        $weekStart = $date->copy()->startOfWeek(Carbon::MONDAY);
        
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $days[] = [
                'date' => $day->format('Y-m-d'),
                'dayName' => $day->format('D'),
                'dayNumber' => $day->day,
                'isToday' => $day->isToday(),
            ];
        }

        return $days;
    }

    /**
     * Get previous week start date
     */
    public function getPreviousWeek(string $currentWeekStart): string
    {
        return Carbon::parse($currentWeekStart)->subWeek()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
    }

    /**
     * Get next week start date
     */
    public function getNextWeek(string $currentWeekStart): string
    {
        return Carbon::parse($currentWeekStart)->addWeek()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
    }
}

