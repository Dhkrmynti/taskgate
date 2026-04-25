<?php

namespace App\Traits;

use App\Models\ProjectState;
use App\Models\UnifiedSubfase;
use App\Models\UnifiedEvidence;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasProjectState
{
    /**
     * Get the project's current state (phase).
     */
    public function projectState(): MorphOne
    {
        return $this->morphOne(ProjectState::class, 'stateable');
    }

    /**
     * Get the project's unified subfase statuses.
     */
    public function unifiedSubfases(): MorphMany
    {
        return $this->morphMany(UnifiedSubfase::class, 'faseable');
    }

    /**
     * Get the project's unified evidence files.
     */
    public function unifiedEvidences(): MorphMany
    {
        return $this->morphMany(UnifiedEvidence::class, 'faseable');
    }
}
