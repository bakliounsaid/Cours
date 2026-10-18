<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            // 0 = dimanche … 6 = samedi (convention Carbon)
            $table->unsignedTinyInteger('day_of_week');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['day_of_week', 'starts_at']);
        });

        DB::statement('ALTER TABLE time_slots ADD CONSTRAINT time_slots_valid_range CHECK (ends_at > starts_at AND day_of_week BETWEEN 0 AND 6)');
    }

    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
