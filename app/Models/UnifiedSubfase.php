<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UnifiedSubfase extends Model
{
    protected $table = 'unified_subfases';

    protected $fillable = [
        'faseable_id',
        'faseable_type',
        'subfase_key',
        'status',
        'notes'
    ];

    public function faseable(): MorphTo
    {
        return $this->morphTo();
    }
}
