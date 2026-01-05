import { useDroppable } from '@dnd-kit/core';
import { Task, Day } from '../Types';
import SortableTaskCard from './SortableTaskCard';
import QuickAddTask from './QuickAddTask';

interface DayColumnProps {
    day: Day;
    tasks: Task[];
}

export default function DayColumn({ day, tasks }: DayColumnProps) {
    const { setNodeRef } = useDroppable({
        id: day.date,
    });

    const dayNames: Record<string, string> = {
        Mon: 'Pon',
        Tue: 'Wt',
        Wed: 'Śr',
        Thu: 'Czw',
        Fri: 'Pt',
        Sat: 'Sob',
        Sun: 'Nd',
    };

    return (
        <div
            ref={setNodeRef}
            className={`flex-1 min-w-[250px] ${day.isToday ? 'bg-blue-50' : 'bg-gray-50'} rounded-lg p-3`}
        >
            <div className="mb-3">
                <div className="text-xs font-medium text-gray-500 uppercase">
                    {dayNames[day.dayName] || day.dayName}
                </div>
                <div className={`text-lg font-semibold ${day.isToday ? 'text-blue-700' : 'text-gray-900'}`}>
                    {day.dayNumber}
                </div>
            </div>

            <div className="space-y-2 mb-3">
                {tasks.map((task) => (
                    <SortableTaskCard key={task.id} task={task} />
                ))}
            </div>

            <QuickAddTask scheduledFor={day.date} />
        </div>
    );
}

