<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceTabSchema extends Model
{
    protected $fillable = ['batch_id', 'tab_key', 'tab_label', 'row_count', 'headers'];
    protected $casts = ['headers' => 'array'];

    public function batch()
    {
        return $this->belongsTo(FinanceImportBatch::class, 'batch_id');
    }
}
