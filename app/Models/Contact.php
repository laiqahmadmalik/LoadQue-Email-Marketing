<?php

namespace App\Models;

use App\Services\PublicIdGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use SoftDeletes;
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

    protected static function booted(): void
    {
        static::creating(function (Contact $contact) {
            if (empty($contact->contact_id)) {
                $contact->contact_id = app(PublicIdGenerator::class)
                    ->generate('CON');
            }
        });

        static::deleting(function (Contact $contact) {
            if (! $contact->isForceDeleting()) {
                $userId = auth()->id();

                \Illuminate\Support\Facades\DB::table('contacts')
                    ->where('id', $contact->getKey())
                    ->update([
                        'deleted_by' => $userId,
                    ]);

                $contact->deleted_by = $userId;
            }
        });
    }

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
