<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectBoqDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'designator',
        'description',
        'volume_planning',
        'price_planning',
        'volume_pemenuhan',
        'volume_aktual',
        'price_aktual',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_planning' => 'decimal:2',
            'price_aktual' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
