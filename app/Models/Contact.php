<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'brokerage_name',
        'contact',
        'cell_phone',
        'email',
        'authorizing_authority',
        'address',
        'city',
        'province_state',
        'zip_code',
        'duplicate_status',
        'broker_status',
        'email_status',
        'subscription_status',
        'unsubscribed_at',
        'email_invalid_at',
        'last_delivered_at',
        'status_notes',
        'phone_duplicate_status',
        'possible_duplicate',
        'duplicate_notes',
    ];

    protected function casts(): array
    {
        return [
            'unsubscribed_at' => 'datetime',
            'email_invalid_at' => 'datetime',
            'last_delivered_at' => 'datetime',
            'possible_duplicate' => 'boolean',
        ];
    }
}
