<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * ASSUMPTION: Task model with UUID primary key
 * ZASADA SPÓJNOŚCI: jeśli project_id != null => bucket automatycznie = 'project'
 */
class Task extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'description',
        'status',
        'starred',
        'due_date',
        'scheduled_for',
        'bucket',
        'project_id',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'starred' => 'boolean',
            'due_date' => 'date',
            'scheduled_for' => 'date',
            'sort_order' => 'float',
        ];
    }

    protected static function booted(): void
    {
        // ZASADA SPÓJNOŚCI: automatyczne ustawienie bucket='project' gdy project_id != null
        static::saving(function (Task $task) {
            if ($task->project_id !== null) {
                $task->bucket = 'project';
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class, 'label_task');
    }
}

