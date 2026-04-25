<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceRecord extends Model
{
    protected $fillable = ['batch_id', 'tab_key', 'row_number', 'project_id', 'data', 'search_text'];
    protected $casts = ['data' => 'array'];

    public function batch()
    {
        return $this->belongsTo(FinanceImportBatch::class, 'batch_id');
    }
}
