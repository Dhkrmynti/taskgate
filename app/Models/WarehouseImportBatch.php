<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseImportBatch extends Model
{
    protected $fillable = ['original_file_name', 'total_rows', 'imported_at'];
    protected $casts = ['imported_at' => 'datetime'];

    public function tabs()
    {
        return $this->hasMany(WarehouseTabSchema::class, 'batch_id');
    }
}
