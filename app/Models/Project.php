<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
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

    protected static function booted()
    {
        static::creating(function ($project) {
            if (empty($project->id)) {
                $project->id = static::generateNextIdentifier();
            }
        });
    }

    public static function generateNextIdentifier(): string
    {
        $sequence = static::nextSequence();
        $datePart = now()->format('Ymd');
        return sprintf('TGIDOP-%s-%04d', $datePart, $sequence);
    }

    protected static function nextSequence(): int
    {
        // Use a more cross-platform approach for sequence extraction
        $formattedIds = \Illuminate\Support\Facades\DB::table('projects')
            ->where('id', 'LIKE', 'TGID-%')
            ->orWhere('id', 'LIKE', 'TGIDOP-%')
            ->pluck('id');

        $maxFromFormatted = $formattedIds->map(function ($id) {
            $parts = explode('-', $id);
            return count($parts) >= 3 ? (int) end($parts) : 0;
        })->max();

        if ($maxFromFormatted > 0) {
            return $maxFromFormatted + 1;
        }

        // Fallback for legacy numeric IDs
        $legacyIds = \Illuminate\Support\Facades\DB::table('projects')
            ->pluck('id')
            ->filter(fn($id) => ctype_digit((string)$id));
            
        $maxFromLegacyNumeric = $legacyIds->map(fn($id) => (int)$id)->max();

        return ($maxFromLegacyNumeric ?? 0) + 1;
    }

    protected $fillable = [
        'id',
        'project_name',
        'pid',
        'wbs_sap',
        'customer',
        'fase',
        'portofolio',
        'program',
        'jenis_eksekusi',
        'branch',
        'pm_project',
        'waspang',
        'evidence_dasar_path',
        'boq_file_path',
        'start_project',
        'end_project',
        'procurement_sp_id',
        'batch_id',
    ];

    protected function casts(): array
    {
        return [
            'start_project' => 'date',
            'end_project' => 'date',
        ];
    }

    public function boqDetails()
    {
        return $this->hasMany(ProjectBoqDetail::class)->orderBy('sort_order');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(ProjectEvidence::class);
    }


    public function constituents(): HasMany
    {
        return $this->hasMany(Project::class, 'procurement_sp_id', 'id');
    }

    public function projectBatch(): BelongsTo
    {
        return $this->belongsTo(ProjectBatch::class, 'batch_id', 'id');
    }

    public function procurementSp(): BelongsTo
    {
        return $this->belongsTo(ProjectBatch::class, 'batch_id', 'id');
    }

    public function getPhaseLabelAttribute(): string
    {
        $timeline = [
            self::PHASE_PLANNING => 'Site Planning',
            self::PHASE_PROCUREMENT => 'Procurement',
            self::PHASE_KONSTRUKSI => 'Konstruksi',
            self::PHASE_REKON => 'Commerce/Rekon',
            self::PHASE_WAREHOUSE => 'Warehouse',
            self::PHASE_FINANCE => 'Finance',
            self::PHASE_CLOSED => 'Selesai/Close',
        ];

        return $timeline[$this->fase] ?? ucwords(str_replace('_', ' ', $this->fase));
    }
}
