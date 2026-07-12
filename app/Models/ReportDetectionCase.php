<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ReportDetectionCase extends Model
{
    use HasFactory;

    public const MATCHING_STATE_OPEN = 'open_for_matching';

    public const MATCHING_STATE_CLOSED = 'closed_for_matching';

    protected $fillable = [
        'case_number',
        'report_type',
        'animal_type',
        'matching_state',
        'matching_window_started_at',
        'matching_window_ends_at',
        'primary_location_text',
        'center_latitude',
        'center_longitude',
    ];

    protected $casts = [
        'matching_window_started_at' => 'datetime',
        'matching_window_ends_at' => 'datetime',
        'center_latitude' => 'decimal:7',
        'center_longitude' => 'decimal:7',
    ];

    public function reports(): BelongsToMany
    {
        return $this->belongsToMany(AnimalReport::class, 'report_detection_case_reports')
            ->withTimestamps();
    }
}