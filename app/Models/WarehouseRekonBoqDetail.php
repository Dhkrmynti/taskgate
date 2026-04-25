<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseRekonBoqDetail extends Model
{
    protected $guarded = [];

    public function rekon()
    {
        return $this->belongsTo(WarehouseRekon::class, 'warehouse_rekon_id');
    }
}
