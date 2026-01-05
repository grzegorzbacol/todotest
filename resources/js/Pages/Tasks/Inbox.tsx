import { PageProps } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { Task } from '../../Types';

export default function Inbox({ tasks }: PageProps<{ tasks: Task[] }>) {
    return (
        <AppLayout>
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h1 className="text-2xl font-bold text-gray-900 mb-6">Inbox</h1>
                    <div className="space-y-2">
                        {tasks.length === 0 ? (
                            <p className="text-gray-500">Brak zadań w inbox.</p>
                        ) : (
                            tasks.map((task) => (
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
                </div>
            </div>
        </AppLayout>
    );
}

