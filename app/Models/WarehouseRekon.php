<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseRekon extends Model
{
    use \App\Traits\HasProjectState;
    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'string';

    public function boqDetails()
    {
        return $this->hasMany(WarehouseRekonBoqDetail::class, 'warehouse_rekon_id');
    }

    public function batches()
    {
        return $this->hasMany(ProjectBatch::class, 'warehouse_rekon_id');
    }

    public function financeRekon()
    {
        return $this->belongsTo(FinanceRekon::class, 'finance_rekon_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function projects()
    {
        return $this->hasManyThrough(Project::class, ProjectBatch::class, 'warehouse_rekon_id', 'batch_id', 'id', 'id');
    }

    public function constituents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->batches();
    }
}
