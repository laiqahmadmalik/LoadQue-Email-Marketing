<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {

            // Phone duplicate tracking
            $table->enum('phone_duplicate_status', ['N', 'D'])
                ->default('N')
                ->after('duplicate_status');

            // Possible duplicate review
            $table->boolean('possible_duplicate')
                ->default(false)
                ->after('phone_duplicate_status');

            $table->text('duplicate_notes')
                ->nullable()
                ->after('possible_duplicate');

        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn([
                'phone_duplicate_status',
                'possible_duplicate',
                'duplicate_notes',
            ]);
        });
    }
};
