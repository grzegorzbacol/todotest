export interface Task {
    id: string;
    title: string;
    description: string | null;
    status: 'active' | 'done';
    starred: boolean;
    due_date: string | null;
    scheduled_for: string | null;
    bucket: 'inbox' | 'single' | 'project';
    project_id: string | null;
    sort_order: number;
    project: {
        id: string;
        name: string;
    } | null;
    labels: Array<{
        id: string;
        name: string;
        color: string | null;
    }>;
}

export interface Day {
    date: string;
    dayName: string;
    dayNumber: number;
    isToday: boolean;
    tasks: Task[];
}

export interface WeekData {
    weekStart: string;
    weekEnd: string;
    days: Day[];
    previousWeek: string;
    nextWeek: string;
}

