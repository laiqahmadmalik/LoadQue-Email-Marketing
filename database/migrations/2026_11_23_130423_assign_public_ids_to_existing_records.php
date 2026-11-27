<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {

            // Assign public IDs to existing users.
            $users = DB::table('users')
                ->orderBy('id')
                ->get(['id']);

            $userNumber = 1;

            foreach ($users as $user) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'user_code' => 'USR' . str_pad(
                            (string) $userNumber,
                            6,
                            '0',
                            STR_PAD_LEFT
                        ),
                    ]);

                $userNumber++;
            }

            // Initialize the next available user ID.
            DB::table('public_id_counters')->updateOrInsert(
                ['prefix' => 'USR'],
                [
                    'next_number' => $userNumber,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            // Assign public IDs to existing contacts.
            $contacts = DB::table('contacts')
                ->orderBy('id')
                ->get(['id']);

            $contactNumber = 1;

            foreach ($contacts as $contact) {
                DB::table('contacts')
                    ->where('id', $contact->id)
                    ->update([
                        'contact_id' => 'CON' . str_pad(
                            (string) $contactNumber,
                            6,
                            '0',
                            STR_PAD_LEFT
                        ),
                    ]);

                $contactNumber++;
            }

            // Initialize the next available contact ID.
            DB::table('public_id_counters')->updateOrInsert(
                ['prefix' => 'CON'],
                [
                    'next_number' => $contactNumber,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        });
    }
    public function down(): void
    {
        // Public IDs must never be reset or reused.
        // Keep assigned IDs and counters if this migration is rolled back.
    }
    /*
    public function down(): void
    {
        DB::transaction(function () {

            DB::table('users')->update([
                'user_code' => null,
            ]);

            DB::table('contacts')->update([
                'contact_id' => null,
            ]);

            DB::table('public_id_counters')
                ->whereIn('prefix', ['USR', 'CON'])
                ->delete();
        });
    }
        */
};
