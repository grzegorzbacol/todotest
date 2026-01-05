<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ASSUMPTION: Project model with UUID primary key and self-referencing parent_id for tree structure
 */
class Project extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'parent_id',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Project::class, 'parent_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}

