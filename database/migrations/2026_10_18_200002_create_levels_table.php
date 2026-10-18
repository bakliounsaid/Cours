<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('cycle', 20);
            $table->unsignedTinyInteger('year');
            $table->jsonb('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['cycle', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
