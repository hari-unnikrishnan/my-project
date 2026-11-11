<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    protected $fillable = [
        'user_id',
        'theme',
        'language',
        'timezone',
        'email_notifications',
        'marketing_emails',
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'marketing_emails' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
