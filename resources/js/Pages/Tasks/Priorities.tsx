import { PageProps } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { Task } from '../../Types';

export default function Priorities({
    todayTasks,
    starredTasks,
    overdueTasks,
}: PageProps<{
    todayTasks: Task[];
    starredTasks: Task[];
    overdueTasks: Task[];
}>) {
    return (
        <AppLayout>
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h1 className="text-2xl font-bold text-gray-900 mb-6">Priorytety</h1>

                    <div className="space-y-8">
                        <section>
                            <h2 className="text-xl font-semibold text-gray-800 mb-4">Dzisiaj</h2>
                            <div className="space-y-2">
                                {todayTasks.length === 0 ? (
                                    <p className="text-gray-500">Brak zadań na dzisiaj.</p>
                                ) : (
                                    todayTasks.map((task) => (
                                        <div
                                            key={task.id}
                                            className="bg-white rounded-lg shadow-sm border border-gray-200 p-4"
                                        >
                                            <h3 className="font-medium text-gray-900">{task.title}</h3>
                                            {task.description && (
                                                <p className="text-sm text-gray-600 mt-1">{task.description}</p>
                                            )}
                                        </div>
                                    ))
                                )}
                            </div>
                        </section>

                        <section>
                            <h2 className="text-xl font-semibold text-gray-800 mb-4">Gwiazdka</h2>
                            <div className="space-y-2">
                                {starredTasks.length === 0 ? (
                                    <p className="text-gray-500">Brak oznaczonych zadań.</p>
                                ) : (
                                    starredTasks.map((task) => (
                                        <div
                                            key={task.id}
                                            className="bg-white rounded-lg shadow-sm border border-gray-200 p-4"
                                        >
                                            <h3 className="font-medium text-gray-900">{task.title}</h3>
                                            {task.description && (
                                                <p className="text-sm text-gray-600 mt-1">{task.description}</p>
                                            )}
                                        </div>
                                    ))
                                )}
                            </div>
                        </section>

                        <section>
                            <h2 className="text-xl font-semibold text-gray-800 mb-4">Zaległe</h2>
                            <div className="space-y-2">
                                {overdueTasks.length === 0 ? (
                                    <p className="text-gray-500">Brak zaległych zadań.</p>
                                ) : (
                                    overdueTasks.map((task) => (
                                        <div
                                            key={task.id}
                                            className="bg-white rounded-lg shadow-sm border border-red-200 p-4"
                                        >
                                            <h3 className="font-medium text-gray-900">{task.title}</h3>
                                            {task.description && (
                                                <p className="text-sm text-gray-600 mt-1">{task.description}</p>
                                            )}
                                            {task.due_date && (
                                                <p className="text-xs text-red-600 mt-2">
                                                    Termin: {new Date(task.due_date).toLocaleDateString('pl-PL')}
                                                </p>
                                            )}
                                        </div>
                                    ))
                                )}
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

