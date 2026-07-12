<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportGroupExclusion extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_type',
        'group_key',
        'report_id',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(AnimalReport::class);
    }
}