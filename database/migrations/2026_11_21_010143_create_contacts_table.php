
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            // Brokerage information
            $table->string('brokerage_name');
            $table->string('contact')->nullable();
            $table->string('cell_phone')->nullable();
            $table->string('email')->nullable()->index();
            $table->enum('duplicate_status', ['N', 'D'])->default('N');
            $table->string('authorizing_authority')->nullable();

            // Address information
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province_state')->nullable();
            $table->string('zip_code')->nullable();

            // Contact status
            $table->enum('broker_status', ['active', 'inactive'])
                  ->default('active');

            $table->enum('email_status', ['unknown', 'valid', 'invalid'])
                  ->default('unknown');

            $table->enum('subscription_status', ['subscribed', 'unsubscribed'])
                  ->default('subscribed');

            // Status tracking
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamp('email_invalid_at')->nullable();
            $table->timestamp('last_delivered_at')->nullable();
            $table->text('status_notes')->nullable();

            $table->timestamps();

            // Search optimization
            $table->index('brokerage_name');
            $table->index('contact');
            $table->index('broker_status');
            $table->index('email_status');
            $table->index('subscription_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
