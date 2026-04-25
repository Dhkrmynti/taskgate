<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectBatchBoqDetail extends Model
{
    protected $fillable = [
        'project_batch_id', 'designator', 'description', 
        'volume_planning', 'price_planning', 'volume_pemenuhan', 
        'volume_aktual', 'price_aktual', 'sort_order'
    ];

    public function batch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProjectBatch::class, 'project_batch_id');
    }
}
