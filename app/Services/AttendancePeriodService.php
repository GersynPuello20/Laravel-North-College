<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Models\AttendanceRecord;

class AttendancePeriodService
{
    public function getCurrentQuincena(): array
    {
        $today = Carbon::today();

        if ($today->day <= 15) {
            return [
                'start' => $today->copy()->startOfMonth(),
                'end' => $today->copy()->day(15),
                'label' => $today->copy()->startOfMonth()->format('d/m/Y') . ' - ' . $today->copy()->day(15)->format('d/m/Y'),
                'status' => 'En curso',
            ];
        }

        return [
            'start' => $today->copy()->day(16),
            'end' => $today->copy()->endOfMonth(),
            'label' => $today->copy()->day(16)->format('d/m/Y') . ' - ' . $today->copy()->endOfMonth()->format('d/m/Y'),
            'status' => 'En curso',
        ];
    }

    public function getLastClosedQuincena(): array
    {
        $today = Carbon::today();

        if ($today->day <= 15) {
            $lastMonth = $today->copy()->subMonthNoOverflow();
            $start = $lastMonth->copy()->day(16)->startOfDay();
            $end = $lastMonth->copy()->endOfMonth()->endOfDay();
        } else {
            $start = $today->copy()->startOfMonth();
            $end = $today->copy()->day(15)->endOfDay();
        }

        return [
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'label' => Carbon::parse($start)->format('d/m/Y') . ' - ' . Carbon::parse($end)->format('d/m/Y'),
        ];
    }

    public function getLast30DaysStats(int $teacherId): array
    {
        $end = Carbon::today();
        $start = $end->copy()->subDays(29);

        $query = AttendanceRecord::with('course')
            ->forTeacher($teacherId)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()]);

        $totalClasses = $query->count();
        $totalAssistants = $query->sum('students_present');
        $totalCourses = $query->distinct('course_id')->count('course_id');
        $averageAttendance = $totalClasses > 0 ? round($totalAssistants / $totalClasses, 2) : 0;

        return [
            'period_label' => $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y'),
            'total_classes' => $totalClasses,
            'total_assistants' => $totalAssistants,
            'total_courses' => $totalCourses,
            'average_attendance' => $averageAttendance,
        ];
    }

    public function parsePeriodKey(?string $key): ?array
    {
        if (! $key) {
            return null;
        }

        if ($key === 'current') {
            return $this->getCurrentQuincena();
        }

        $parts = explode('_', $key);

        if (count($parts) !== 2) {
            return null;
        }

        try {
            $start = Carbon::parse($parts[0])->startOfDay();
            $end = Carbon::parse($parts[1])->endOfDay();
        } catch (\Exception $e) {
            return null;
        }

        if ($start->greaterThan($end)) {
            return null;
        }

        return [
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'label' => $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y'),
        ];
    }

    public function getClosedPeriods(int $months = 3): Collection
    {
        $periods = collect();
        $today = Carbon::today();

        for ($index = 0; $index < $months; $index++) {
            $current = $today->copy()->subMonthNoOverflow($index);
            $firstHalfStart = $current->copy()->startOfMonth();
            $firstHalfEnd = $current->copy()->day(15);
            $secondHalfStart = $current->copy()->day(16);
            $secondHalfEnd = $current->copy()->endOfMonth();

            if ($firstHalfEnd->lessThanOrEqualTo($today)) {
                $periods->push([
                    'start' => $firstHalfStart->toDateString(),
                    'end' => $firstHalfEnd->toDateString(),
                    'label' => $firstHalfStart->format('d/m/Y') . ' - ' . $firstHalfEnd->format('d/m/Y'),
                ]);
            }

            if ($secondHalfEnd->lessThanOrEqualTo($today)) {
                $periods->push([
                    'start' => $secondHalfStart->toDateString(),
                    'end' => $secondHalfEnd->toDateString(),
                    'label' => $secondHalfStart->format('d/m/Y') . ' - ' . $secondHalfEnd->format('d/m/Y'),
                ]);
            }
        }

        return $periods->unique(fn ($period) => $period['start'].'_'.$period['end'])->sortByDesc(fn ($period) => $period['start'])->values();
    }
};
