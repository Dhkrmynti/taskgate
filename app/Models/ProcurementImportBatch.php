<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementImportBatch extends Model
{
    protected $casts = [
        'imported_at' => 'datetime',
    ];
}
