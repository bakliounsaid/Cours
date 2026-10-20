<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('status', 10);
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['lesson_id', 'student_id']);
            $table->index('student_id');
        });

        Schema::create('teacher_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->restrictOnDelete();
            $table->dateTime('checked_at');
            // Tarif figé au moment du pointage (pour la paie)
            $table->unsignedInteger('rate_amount');
            $table->foreignId('corrected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['teacher_id', 'checked_at']);
        });

        // Journal de la séance : chaque action (pointage, appel, modification, travail saisi)
        // est conservée avec son auteur, sa date et les valeurs avant / après.
        Schema::create('lesson_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 30);
            $table->jsonb('data')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['lesson_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_events');
        Schema::dropIfExists('teacher_checkins');
        Schema::dropIfExists('attendances');
    }
};
