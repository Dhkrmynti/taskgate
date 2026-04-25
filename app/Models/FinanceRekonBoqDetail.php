<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceRekonBoqDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'finance_rekon_id',
        'designator',
        'description',
        'volume',
        'price',
        'sort_order'
    ];

    public function financeRekon()
    {
        return $this->belongsTo(FinanceRekon::class, 'finance_rekon_id', 'id');
    }
}
