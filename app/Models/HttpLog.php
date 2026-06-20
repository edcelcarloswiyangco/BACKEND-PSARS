<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HttpLog extends Model
{
    protected $fillable = [
        'method',
        'path',
        'ip_address',
        'status',
        'duration_ms'
    ];
}