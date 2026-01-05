import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { Task } from '../Types';
import TaskCard from './TaskCard';

interface SortableTaskCardProps {
    task: Task;
}

export default function SortableTaskCard({ task }: SortableTaskCardProps) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({
        id: task.id,
    });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        opacity: isDragging ? 0.5 : 1,
    };

    return (
        <div ref={setNodeRef} style={style}>
            <div {...attributes} {...listeners} className="cursor-grab active:cursor-grabbing">
                <TaskCard task={task} />
            </div>
        </div>
    );
}

