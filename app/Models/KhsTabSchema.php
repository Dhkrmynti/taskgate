<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KhsTabSchema extends Model
{
    protected $fillable = [
        'batch_id',
        'tab_key',
        'tab_label',
        'row_count',
        'headers',
    ];

    protected $casts = [
        'headers' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(KhsImportBatch::class, 'batch_id');
    }
}
