<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KhsImportBatch extends Model
{
    protected $fillable = [
        'original_file_name',
        'total_rows',
        'imported_at',
    ];

    protected $casts = [
        'imported_at' => 'datetime',
    ];

    public function tabs(): HasMany
    {
        return $this->hasMany(KhsTabSchema::class, 'batch_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(KhsRecord::class, 'batch_id');
    }
}
