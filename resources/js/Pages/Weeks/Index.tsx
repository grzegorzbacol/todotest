import { PageProps } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import WeekHeader from '../../Components/WeekHeader';
import WeekBoard from '../../Components/WeekBoard';
import { WeekData } from '../../Types';

export default function Index({ weekStart, weekEnd, days, previousWeek, nextWeek }: PageProps<WeekData>) {
    return (
        <AppLayout>
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <WeekHeader
                        weekStart={weekStart}
                        previousWeek={previousWeek}
                        nextWeek={nextWeek}
                    />
                    <div className="mt-6">
                        <WeekBoard
                            data={{
                                weekStart,
                                weekEnd,
                                days,
                                previousWeek,
                                nextWeek,
                            }}
                        />
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

