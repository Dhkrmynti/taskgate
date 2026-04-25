<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommerceImportBatch extends Model
{
    protected $casts = [
        'imported_at' => 'datetime',
    ];
}
