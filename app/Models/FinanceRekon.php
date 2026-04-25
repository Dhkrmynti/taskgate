<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceRekon extends Model
{
    use \App\Traits\HasProjectState;
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'fase',
        'apm_number',
        'evidence_path',
        'boq_file_path',
        'total_amount',
        'created_by'
    ];

    public function commerceRekons()
    {
        return $this->hasMany(CommerceRekon::class, 'finance_rekon_id', 'id');
    }

    public function warehouseRekons()
    {
        return $this->hasMany(WarehouseRekon::class, 'finance_rekon_id', 'id');
    }

    public function boqDetails()
    {
        return $this->hasMany(FinanceRekonBoqDetail::class, 'finance_rekon_id', 'id')->orderBy('sort_order');
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function constituents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->commerceRekons();
    }
}
