import {
    DndContext,
    DragEndEvent,
    DragOverlay,
    DragStartEvent,
    PointerSensor,
    useSensor,
    useSensors,
    closestCorners,
} from '@dnd-kit/core';
import {
    SortableContext,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { WeekData, Task, Day } from '../Types';
import DayColumn from './DayColumn';
import TaskCard from './TaskCard';

interface WeekBoardProps {
    data: WeekData;
}

export default function WeekBoard({ data }: WeekBoardProps) {
    const [activeTask, setActiveTask] = useState<Task | null>(null);
    const [optimisticDays, setOptimisticDays] = useState<Day[]>(data.days);

    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: {
                distance: 8,
            },
        })
    );

    const handleDragStart = (event: DragStartEvent) => {
        const { active } = event;
        const taskId = active.id as string;
        
        // Find task in all days
        let task: Task | null = null;
        for (const day of optimisticDays) {
            const found = day.tasks.find((t) => t.id === taskId);
            if (found) {
                task = found;
                break;
            }
        }
        
        setActiveTask(task);
    };

    const handleDragEnd = async (event: DragEndEvent) => {
        const { active, over } = event;
        
        if (!over) {
            setActiveTask(null);
            return;
        }

        const taskId = active.id as string;
        const sourceDay = optimisticDays.find((day) =>
            day.tasks.some((task) => task.id === taskId)
        );
        const targetDay = optimisticDays.find((day) => day.date === over.id);

        if (!sourceDay || !targetDay) {
            setActiveTask(null);
            return;
        }

        const sourceIndex = sourceDay.tasks.findIndex((task) => task.id === taskId);
        const task = sourceDay.tasks[sourceIndex];

        if (!task) {
            setActiveTask(null);
            return;
        }

        // Optimistic update
        const newDays = optimisticDays.map((day) => {
            if (day.date === sourceDay.date) {
                return {
                    ...day,
                    tasks: day.tasks.filter((t) => t.id !== taskId),
                };
            }
            if (day.date === targetDay.date) {
                const newTask = { ...task, scheduled_for: targetDay.date };
                return {
                    ...day,
                    tasks: [...day.tasks, newTask],
                };
            }
            return day;
        });

        setOptimisticDays(newDays);
        setActiveTask(null);

        // Update task on server
        try {
            const response = await fetch(`/api/tasks/${taskId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    scheduled_for: targetDay.date,
                }),
            });

            if (!response.ok) {
                throw new Error('Failed to update task');
            }

            // Reload to get updated sort_order
            router.reload({ only: ['days'] });
        } catch (error) {
            console.error('Error updating task:', error);
            // Rollback on error
            router.reload({ only: ['days'] });
        }
    };

    // Update optimistic days when data changes
    useEffect(() => {
        setOptimisticDays(data.days);
    }, [data.weekStart]); // Use weekStart as dependency instead of days array

    return (
        <DndContext
            sensors={sensors}
            collisionDetection={closestCorners}
            onDragStart={handleDragStart}
            onDragEnd={handleDragEnd}
        >
            <div className="flex space-x-4 overflow-x-auto pb-4">
                {optimisticDays.map((day) => (
                    <SortableContext
                        key={day.date}
                        id={day.date}
                        items={day.tasks.map((task) => task.id)}
                        strategy={verticalListSortingStrategy}
                    >
                        <DayColumn day={day} tasks={day.tasks} />
                    </SortableContext>
                ))}
            </div>

            <DragOverlay>
                {activeTask ? (
                    <div className="opacity-50">
                        <TaskCard task={activeTask} />
                    </div>
                ) : null}
            </DragOverlay>
        </DndContext>
    );
}

