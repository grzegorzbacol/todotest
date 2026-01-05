<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\SortOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TasksController extends Controller
{
    public function __construct(
        private SortOrderService $sortOrderService
    ) {}

    /**
     * API endpoint: Create a new task
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_for' => 'nullable|date',
            'bucket' => 'nullable|in:inbox,single,project',
            'project_id' => 'nullable|uuid|exists:projects,id',
            'due_date' => 'nullable|date',
            'sort_order' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // ASSUMPTION: Default bucket='inbox' if no project_id
        if (!isset($data['bucket']) && !isset($data['project_id'])) {
            $data['bucket'] = 'inbox';
        }

        // ASSUMPTION: Set sort_order if not provided and scheduled_for is set
        if (!isset($data['sort_order']) && isset($data['scheduled_for'])) {
            $data['sort_order'] = $this->sortOrderService->getNextSortOrder($data['scheduled_for']);
        }

        $task = Task::create($data);
        $task->load(['project:id,name', 'labels:id,name,color']);

        return response()->json($this->formatTask($task), 201);
    }

    /**
     * API endpoint: Update a task
     */
    public function update(Request $request, string $id)
    {
        $task = Task::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:active,done',
            'starred' => 'sometimes|boolean',
            'due_date' => 'nullable|date',
            'scheduled_for' => 'nullable|date',
            'sort_order' => 'nullable|numeric',
            'bucket' => 'nullable|in:inbox,single,project',
            'project_id' => 'nullable|uuid|exists:projects,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $task->update($data);
        $task->load(['project:id,name', 'labels:id,name,color']);

        return response()->json($this->formatTask($task));
    }

    /**
     * Display inbox view
     */
    public function inbox()
    {
        $tasks = Task::where('bucket', 'inbox')
            ->where('status', 'active')
            ->with(['project:id,name', 'labels:id,name,color'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($task) => $this->formatTask($task));

        return \Inertia\Inertia::render('Tasks/Inbox', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * Display single tasks view
     */
    public function single()
    {
        $tasks = Task::where('bucket', 'single')
            ->where('status', 'active')
            ->with(['project:id,name', 'labels:id,name,color'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($task) => $this->formatTask($task));

        return \Inertia\Inertia::render('Tasks/Single', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * Display priorities view
     */
    public function priorities()
    {
        $today = now()->format('Y-m-d');

        $todayTasks = Task::where('status', 'active')
            ->where('scheduled_for', $today)
            ->with(['project:id,name', 'labels:id,name,color'])
            ->orderBy('sort_order')
            ->get()
            ->map(fn($task) => $this->formatTask($task));

        $starredTasks = Task::where('status', 'active')
            ->where('starred', true)
            ->with(['project:id,name', 'labels:id,name,color'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($task) => $this->formatTask($task));

        $overdueTasks = Task::where('status', 'active')
            ->whereNotNull('due_date')
            ->where('due_date', '<', $today)
            ->with(['project:id,name', 'labels:id,name,color'])
            ->orderBy('due_date')
            ->get()
            ->map(fn($task) => $this->formatTask($task));

        return \Inertia\Inertia::render('Tasks/Priorities', [
            'todayTasks' => $todayTasks,
            'starredTasks' => $starredTasks,
            'overdueTasks' => $overdueTasks,
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

