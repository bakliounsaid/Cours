<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 30);
            $table->string('channel', 20);
            $table->string('locale', 2);
            $table->string('phone', 20);
            $table->text('body');
            $table->string('status', 10);
            $table->text('error')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('sent_at');
            $table->timestamps();

            $table->index(['student_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_messages');
    }
};
