<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KhsRecord extends Model
{
    protected $fillable = [
        'batch_id',
        'tab_key',
        'row_number',
        'data',
        'search_text',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(KhsImportBatch::class, 'batch_id');
    }
}
