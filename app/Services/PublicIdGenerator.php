<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PublicIdGenerator
{
    /**
     * Generate a unique public ID.
     *
     * Examples:
     * USR000001
     * CON000001
     * CAM000001
     */
    public function generate(string $prefix): string
    {
        $prefix = strtoupper(trim($prefix));

        if (!preg_match('/^[A-Z]{3}$/', $prefix)) {
            throw new InvalidArgumentException(
                'Public ID prefix must contain exactly three letters.'
            );
        }

        return DB::transaction(function () use ($prefix) {

            // Create the counter if it does not exist.
            DB::table('public_id_counters')->insertOrIgnore([
                'prefix' => $prefix,
                'next_number' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Lock this prefix counter during generation.
            $counter = DB::table('public_id_counters')
                ->where('prefix', $prefix)
                ->lockForUpdate()
                ->first();

            $number = $counter->next_number;

            if ($number > 999999) {
                throw new InvalidArgumentException(
                    "The {$prefix} ID sequence has reached its limit."
                );
            }

            // Advance the counter.
            DB::table('public_id_counters')
                ->where('prefix', $prefix)
                ->update([
                    'next_number' => $number + 1,
                    'updated_at' => now(),
                ]);

            // Return the formatted ID.
            return $prefix . str_pad(
                (string) $number,
                6,
                '0',
                STR_PAD_LEFT
            );
        });
    }
}
