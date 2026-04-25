<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProjectState extends Model
{
    protected $fillable = [
        'stateable_id',
        'stateable_type',
        'current_phase',
        'history'
    ];

    protected $casts = [
        'history' => 'array'
    ];

    /**
     * Get the parent stateable model (Project, ProjectBatch, CommerceRekon, etc.).
     */
    public function stateable(): MorphTo
    {
        return $this->morphTo();
    }
}
