<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationMessage extends Model
{
    protected $table = 'application_messages';

    protected $fillable = [
        'message_code',
        'message_type',
        'language',
        'message',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'message_code' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
