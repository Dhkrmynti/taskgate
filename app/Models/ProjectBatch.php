<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectBatch extends Model
{
    use HasFactory, \App\Traits\HasProjectState;

    const PHASE_PLANNING = 'planning';
    const PHASE_PROCUREMENT = 'procurement';
    const PHASE_KONSTRUKSI = 'konstruksi';
    const PHASE_REKON = 'rekon';
    const PHASE_WAREHOUSE = 'warehouse';
    const PHASE_FINANCE = 'finance';
    const PHASE_CLOSED = 'closed';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'project_name',
        'po_number',
        'mitra_id',
        'customer',
        'branch',
        'fase',
        'boq_file_path',
        'dasar_pekerjaan_file_path',
        'rekon_id',
        'warehouse_rekon_id',
        'created_by'
    ];

    public function mitra(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Mitra::class, 'mitra_id');
    }

    public function projects(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Project::class, 'batch_id', 'id');
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function commerceRekon(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CommerceRekon::class, 'rekon_id', 'id');
    }

    public function warehouseRekon(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WarehouseRekon::class, 'warehouse_rekon_id', 'id');
    }

    public function boqDetails(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectBatchBoqDetail::class, 'project_batch_id', 'id')->orderBy('sort_order');
    }

    public function getPhaseLabelAttribute(): string
    {
        $timeline = [
            self::PHASE_PLANNING => 'Planning',
            self::PHASE_PROCUREMENT => 'Procurement',
            self::PHASE_KONSTRUKSI => 'Konstruksi',
            self::PHASE_REKON => 'Commerce',
            self::PHASE_WAREHOUSE => 'Warehouse',
            self::PHASE_FINANCE => 'Finance',
            self::PHASE_CLOSED => 'Closed',
        ];

        return $timeline[$this->fase] ?? ucwords(str_replace('_', ' ', $this->fase));
    }

    public function constituents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->projects();
    }
}
