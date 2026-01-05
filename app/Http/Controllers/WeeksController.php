<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\WeekService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WeeksController extends Controller
{
    public function __construct(
        private WeekService $weekService
    ) {}

    /**
     * Display weeks kanban view
     */
    public function index(Request $request): Response
    {
        $startDate = $request->query('start');
        $weekBounds = $this->weekService->getWeekBounds($startDate);
        $days = $this->weekService->getWeekDays($startDate);

        // Load tasks for each day
        $tasksByDate = Task::where('status', 'active')
            ->whereBetween('scheduled_for', [$weekBounds['weekStart'], $weekBounds['weekEnd']])
            ->with(['project:id,name', 'labels:id,name,color'])
            ->orderBy('sort_order')
            ->get()
            ->groupBy('scheduled_for');

        // Map tasks to days
        foreach ($days as &$day) {
            $day['tasks'] = $tasksByDate->get($day['date'], collect())
                ->map(fn($task) => $this->formatTask($task))
                ->values()
                ->toArray();
        }

        return Inertia::render('Weeks/Index', [
            'weekStart' => $weekBounds['weekStart'],
            'weekEnd' => $weekBounds['weekEnd'],
            'days' => $days,
            'previousWeek' => $this->weekService->getPreviousWeek($weekBounds['weekStart']),
            'nextWeek' => $this->weekService->getNextWeek($weekBounds['weekStart']),
        ]);
    }

    /**
     * API endpoint: Get week data
     */
    public function getWeek(Request $request)
    {
        $startDate = $request->query('start');
        $weekBounds = $this->weekService->getWeekBounds($startDate);
        $days = $this->weekService->getWeekDays($startDate);

        $tasksByDate = Task::where('status', 'active')
            ->whereBetween('scheduled_for', [$weekBounds['weekStart'], $weekBounds['weekEnd']])
            ->with(['project:id,name', 'labels:id,name,color'])
            ->orderBy('sort_order')
            ->get()
            ->groupBy('scheduled_for');

        foreach ($days as &$day) {
            $day['tasks'] = $tasksByDate->get($day['date'], collect())
                ->map(fn($task) => $this->formatTask($task))
                ->values()
                ->toArray();
        }

        return response()->json([
            'weekStart' => $weekBounds['weekStart'],
            'weekEnd' => $weekBounds['weekEnd'],
            'days' => $days,
        ]);
    }

    private function formatTask(Task $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'starred' => $task->starred,
            'due_date' => $task->due_date?->format('Y-m-d'),
            'scheduled_for' => $task->scheduled_for?->format('Y-m-d'),
            'bucket' => $task->bucket,
            'project_id' => $task->project_id,
            'sort_order' => $task->sort_order,
            'project' => $task->project ? [
                'id' => $task->project->id,
                'name' => $task->project->name,
            ] : null,
            'labels' => $task->labels->map(fn($label) => [
                'id' => $label->id,
                'name' => $label->name,
                'color' => $label->color,
            ])->toArray(),
        ];
    }
}

