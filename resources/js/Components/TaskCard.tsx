import { Task } from '../Types';
import { router } from '@inertiajs/react';
import { useState } from 'react';

interface TaskCardProps {
    task: Task;
}

export default function TaskCard({ task }: TaskCardProps) {
    const [isUpdating, setIsUpdating] = useState(false);

    const toggleStarred = async () => {
        if (isUpdating) return;
        setIsUpdating(true);
        try {
            await fetch(`/api/tasks/${task.id}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ starred: !task.starred }),
            });
            router.reload({ only: ['days'] });
        } catch (error) {
            console.error('Error toggling starred:', error);
        } finally {
            setIsUpdating(false);
        }
    };

    const toggleStatus = async () => {
        if (isUpdating) return;
        setIsUpdating(true);
        try {
            await fetch(`/api/tasks/${task.id}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ status: task.status === 'active' ? 'done' : 'active' }),
            });
            router.reload({ only: ['days'] });
        } catch (error) {
            console.error('Error toggling status:', error);
        } finally {
            setIsUpdating(false);
        }
    };

    const formatDate = (dateString: string | null) => {
        if (!dateString) return null;
        const date = new Date(dateString);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const taskDate = new Date(date);
        taskDate.setHours(0, 0, 0, 0);
        
        if (taskDate < today) {
            return { text: date.toLocaleDateString('pl-PL', { day: 'numeric', month: 'short' }), isOverdue: true };
        }
        return { text: date.toLocaleDateString('pl-PL', { day: 'numeric', month: 'short' }), isOverdue: false };
    };

    const dueDateInfo = formatDate(task.due_date);
    const visibleLabels = task.labels.slice(0, 2);
    const remainingLabelsCount = task.labels.length - 2;

    return (
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-3 hover:shadow-md transition-shadow">
            <div className="flex items-start justify-between mb-2">
                <div className="flex items-center space-x-2 flex-1">
                    <input
                        type="checkbox"
                        checked={task.status === 'done'}
                        onChange={toggleStatus}
                        className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <h3 className={`text-sm font-medium flex-1 ${task.status === 'done' ? 'line-through text-gray-500' : 'text-gray-900'}`}>
                        {task.title}
                    </h3>
                </div>
                <button
                    onClick={toggleStarred}
                    className={`ml-2 ${task.starred ? 'text-yellow-500' : 'text-gray-400'} hover:text-yellow-600`}
                    disabled={isUpdating}
                >
                    <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </button>
            </div>

            {task.description && (
                <p className="text-xs text-gray-600 mb-2 line-clamp-2">{task.description}</p>
            )}

            <div className="flex items-center flex-wrap gap-1 mt-2">
                {dueDateInfo && (
                    <span className={`text-xs px-2 py-0.5 rounded ${
                        dueDateInfo.isOverdue 
                            ? 'bg-red-100 text-red-800' 
                            : 'bg-blue-100 text-blue-800'
                    }`}>
                        {dueDateInfo.text}
                    </span>
                )}
                {task.project && (
                    <span className="text-xs px-2 py-0.5 rounded bg-purple-100 text-purple-800">
                        {task.project.name}
                    </span>
                )}
                {visibleLabels.map((label) => (
                    <span
                        key={label.id}
                        className="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-800"
                        style={label.color ? { backgroundColor: `${label.color}20`, color: label.color } : {}}
                    >
                        {label.name}
                    </span>
                ))}
                {remainingLabelsCount > 0 && (
                    <span className="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-600">
                        +{remainingLabelsCount}
                    </span>
                )}
            </div>
        </div>
    );
}

