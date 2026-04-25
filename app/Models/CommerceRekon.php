<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommerceRekon extends Model
{
    use \App\Traits\HasProjectState;
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'fase',
        'finance_rekon_id',
        'rekon_number',
        'rekon_file_path',
        'boq_file_path',
        'created_by'
    ];

    public function financeRekon(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FinanceRekon::class, 'finance_rekon_id', 'id');
    }

    public function batches(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectBatch::class, 'rekon_id', 'id');
    }

    public function boqDetails()
    {
        return $this->hasMany(CommerceRekonBoqDetail::class, 'commerce_rekon_id', 'id')->orderBy('sort_order');
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function projects()
    {
        return $this->hasManyThrough(Project::class, ProjectBatch::class, 'rekon_id', 'batch_id', 'id', 'id');
    }

    public function constituents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->batches();
    }
}
