<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('time_slot_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->constrained()->restrictOnDelete();
            $table->foreignId('teacher_id')->constrained()->restrictOnDelete();
            // Enseignant prévu à l'origine, en cas de remplacement
            $table->foreignId('original_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->foreignId('makeup_of_id')->nullable()->constrained('lessons')->nullOnDelete();
            $table->date('date');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('type', 20);
            $table->string('status', 20)->default('scheduled');
            $table->string('title', 120)->nullable();
            $table->text('cancel_reason')->nullable();
            // Travail réalisé pendant la séance et devoirs donnés (traçabilité pédagogique)
            $table->text('summary')->nullable();
            $table->text('homework')->nullable();
            $table->boolean('missing_attendance_alerted')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['date', 'status']);
            $table->index(['teacher_id', 'starts_at']);
        });

        // Séances individuelles / spéciales : élèves hors groupe
        Schema::create('lesson_student', function (Blueprint $table) {
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->primary(['lesson_id', 'student_id']);
            $table->index('student_id');
        });

        DB::statement('ALTER TABLE lessons ADD CONSTRAINT lessons_valid_range CHECK (ends_at > starts_at)');

        // Anti-conflits garantis par la base : une salle / un enseignant ne peut pas avoir
        // deux séances actives qui se chevauchent.
        DB::statement("ALTER TABLE lessons ADD CONSTRAINT lessons_room_no_overlap EXCLUDE USING gist (room_id WITH =, tsrange(starts_at, ends_at) WITH &&) WHERE (status <> 'cancelled')");
        DB::statement("ALTER TABLE lessons ADD CONSTRAINT lessons_teacher_no_overlap EXCLUDE USING gist (teacher_id WITH =, tsrange(starts_at, ends_at) WITH &&) WHERE (status <> 'cancelled')");

        // La génération automatique ne crée jamais deux fois la même séance de groupe.
        DB::statement("CREATE UNIQUE INDEX lessons_group_occurrence_unique ON lessons (group_id, starts_at) WHERE type = 'group'");
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_student');
        Schema::dropIfExists('lessons');
    }
};
