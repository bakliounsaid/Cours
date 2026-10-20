<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            // Les séances sont comptées et payées par enseignant (cycle de N séances chez lui)
            $table->foreignId('teacher_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('amount');
            $table->dateTime('paid_at');
            $table->string('method', 20);
            $table->unsignedTinyInteger('lessons_covered')->default(4);
            $table->string('receipt_number', 20)->nullable()->unique();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'paid_at']);
            $table->index(['teacher_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
