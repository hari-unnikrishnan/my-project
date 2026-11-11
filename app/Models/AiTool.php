<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiTool extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'icon',
        'accent_class',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
