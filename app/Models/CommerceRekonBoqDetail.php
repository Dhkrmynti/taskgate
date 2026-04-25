<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommerceRekonBoqDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'commerce_rekon_id',
        'designator',
        'description',
        'volume_planning',
        'price_planning',
        'volume_pemenuhan',
        'volume_aktual',
        'price_aktual',
        'sort_order'
    ];

    public function rekon(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CommerceRekon::class, 'commerce_rekon_id');
    }
}
