<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceImportBatch extends Model
{
    protected $fillable = ['original_file_name', 'total_rows', 'imported_at'];
    protected $casts = ['imported_at' => 'datetime'];

    public function tabs()
    {
        return $this->hasMany(FinanceTabSchema::class, 'batch_id');
    }
}
