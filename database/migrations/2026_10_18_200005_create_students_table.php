<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 80);
            $table->string('last_name', 80);
            $table->foreignId('level_id')->constrained()->restrictOnDelete();
            $table->string('parent_phone', 20);
            $table->string('parent_locale', 2)->default('fr');
            $table->date('enrolled_at');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'last_name', 'first_name']);
        });

        Schema::create('student_subject', function (Blueprint $table) {
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->primary(['student_id', 'subject_id']);
        });

        // Élèves suivis par un enseignant en cours individuels
        Schema::create('student_teacher', function (Blueprint $table) {
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->primary(['student_id', 'teacher_id']);
            $table->index('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_teacher');
        Schema::dropIfExists('student_subject');
        Schema::dropIfExists('students');
    }
};
