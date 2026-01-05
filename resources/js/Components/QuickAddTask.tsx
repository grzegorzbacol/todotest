import { useState, KeyboardEvent } from 'react';
import { router } from '@inertiajs/react';

interface QuickAddTaskProps {
    scheduledFor: string;
    onTaskAdded?: () => void;
}

export default function QuickAddTask({ scheduledFor, onTaskAdded }: QuickAddTaskProps) {
    const [title, setTitle] = useState('');
    const [isAdding, setIsAdding] = useState(false);
    const [isFocused, setIsFocused] = useState(false);

    const handleSubmit = async () => {
        if (!title.trim() || isAdding) return;

        setIsAdding(true);
        try {
            await fetch('/api/tasks', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    title: title.trim(),
                    scheduled_for: scheduledFor,
                    bucket: 'inbox',
                }),
            });
            setTitle('');
            setIsFocused(false);
            router.reload({ only: ['days'] });
            onTaskAdded?.();
        } catch (error) {
            console.error('Error adding task:', error);
        } finally {
            setIsAdding(false);
        }
    };

    const handleKeyDown = (e: KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleSubmit();
        } else if (e.key === 'Escape') {
            setTitle('');
            setIsFocused(false);
        }
    };

    if (!isFocused && !title) {
        return (
            <button
                onClick={() => setIsFocused(true)}
                className="w-full text-left text-sm text-gray-500 hover:text-gray-700 py-2 px-3 rounded hover:bg-gray-50 transition-colors"
            >
                + Dodaj zadanie
            </button>
        );
    }

    return (
        <input
            type="text"
            value={title}
            onChange={(e) => setTitle(e.target.value)}
            onKeyDown={handleKeyDown}
            onBlur={() => {
                if (!title.trim()) {
                    setIsFocused(false);
                }
            }}
            autoFocus
            placeholder="Nazwa zadania..."
            className="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            disabled={isAdding}
        />
    );
}

