<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = [
        'phone',
        'whatsapp',
        'email',
        'tiktok',
        'facebook',
        'instagram',
        'youtube',
    ];

    protected $casts = [
        'phone' => 'array',
    ];
}

