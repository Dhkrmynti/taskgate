<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UnifiedEvidence extends Model
{
    protected $table = 'unified_evidences';

    protected $fillable = [
        'faseable_id',
        'faseable_type',
        'type',
        'file_name',
        'file_path',
        'file_extension',
        'file_size'
    ];

    public function faseable(): MorphTo
    {
        return $this->morphTo();
    }
}
