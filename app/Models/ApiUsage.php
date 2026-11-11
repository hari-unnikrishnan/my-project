<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiUsage extends Model
{
    protected $table = 'api_usage';

    protected $fillable = [
        'user_id',
        'tool_name',
        'requests_used',
        'source',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
