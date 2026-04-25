<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseTabSchema extends Model
{
    protected $fillable = ['batch_id', 'tab_key', 'tab_label', 'row_count', 'headers'];
    protected $casts = ['headers' => 'array'];

    public function batch()
    {
        return $this->belongsTo(WarehouseImportBatch::class, 'batch_id');
    }
}
