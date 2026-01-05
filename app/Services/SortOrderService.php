<?php

namespace App\Services;

use App\Models\Task;

/**
 * ASSUMPTION: SortOrderService manages sort_order values for tasks
 * Uses float values to allow insertion between items
 */
class SortOrderService
{
    /**
     * Get next sort_order value for a column (scheduled_for date)
     */
    public function getNextSortOrder(?string $scheduledFor): float
    {
        $maxOrder = Task::where('scheduled_for', $scheduledFor)
            ->max('sort_order');

        return ($maxOrder ?? 0) + 1.0;
    }

    /**
     * Reorder tasks in a column after drag & drop
     * ASSUMPTION: Simple approach - assign sequential values
     * TODO: Consider more sophisticated reordering with gaps for future insertions
     */
    public function reorderTasks(array $taskIds, ?string $scheduledFor): void
    {
        foreach ($taskIds as $index => $taskId) {
            Task::where('id', $taskId)
                ->where('scheduled_for', $scheduledFor)
                ->update(['sort_order' => ($index + 1) * 1.0]);
        }
    }
}

