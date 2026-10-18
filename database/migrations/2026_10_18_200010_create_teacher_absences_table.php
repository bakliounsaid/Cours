<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('scope', 10);
            $table->text('reason');
            $table->dateTime('handled_at')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'date']);
        });

        Schema::create('lesson_teacher_absence', function (Blueprint $table) {
            $table->foreignId('teacher_absence_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('resolution', 20)->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->primary(['teacher_absence_id', 'lesson_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_teacher_absence');
        Schema::dropIfExists('teacher_absences');
    }
};
