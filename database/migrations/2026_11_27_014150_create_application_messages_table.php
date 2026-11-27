<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('application_messages', function (Blueprint $table) {
            $table->id();

            // Numeric application message code
            $table->unsignedInteger('message_code');

            // Message category: error or success
            $table->string('message_type', 20);

            // Language identifier: en, fr, etc.
            $table->string('language', 10)->default('en');

            // Message displayed to the user
            $table->text('message');

            // Enable or disable individual messages
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Same message code can have translations,
            // but cannot be duplicated within the same language.
            $table->unique(
                ['message_code', 'language'],
                'application_messages_code_language_unique'
            );

            $table->index(
                ['message_type', 'is_active'],
                'application_messages_type_active_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_messages');
    }
};
