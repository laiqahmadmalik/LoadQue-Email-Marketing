<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_id_counters', function (Blueprint $table) {
            $table->string('prefix', 3)->primary();

            $table->unsignedBigInteger('next_number')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_id_counters');
    }
};
