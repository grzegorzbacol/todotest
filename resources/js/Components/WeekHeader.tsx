import { router } from '@inertiajs/react';

interface WeekHeaderProps {
    weekStart: string;
    previousWeek: string;
    nextWeek: string;
}

export default function WeekHeader({ weekStart, previousWeek, nextWeek }: WeekHeaderProps) {
    const formatDate = (dateString: string) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('pl-PL', { day: 'numeric', month: 'short' });
    };

    const goToToday = () => {
        router.get('/weeks');
    };

    const goToPreviousWeek = () => {
        router.get(`/weeks?start=${previousWeek}`);
    };

    const goToNextWeek = () => {
        router.get(`/weeks?start=${nextWeek}`);
    };

    const startDate = new Date(weekStart);
    const endDate = new Date(startDate);
    endDate.setDate(endDate.getDate() + 6);

    return (
        <div className="bg-white border-b border-gray-200 px-4 py-3">
            <div className="flex items-center justify-between">
                <div className="flex items-center space-x-4">
                    <button
                        onClick={goToPreviousWeek}
                        className="px-3 py-1 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded"
                    >
                        ← Poprzedni
                    </button>
                    <button
                        onClick={goToToday}
                        className="px-3 py-1 text-sm font-medium text-blue-600 hover:bg-blue-50 rounded"
                    >
                        Dzisiaj
                    </button>
                    <button
                        onClick={goToNextWeek}
                        className="px-3 py-1 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded"
                    >
                        Następny →
                    </button>
                </div>
                <div className="text-sm font-medium text-gray-700">
                    {formatDate(weekStart)} - {formatDate(endDate.toISOString().split('T')[0])}
                </div>
            </div>
        </div>
    );
}

