<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollment_requests', function (Blueprint $table) {
            $table->id();
            $table->string('student_name', 160);
            $table->foreignId('level_id')->nullable()->constrained()->nullOnDelete();
            $table->jsonb('subject_ids')->nullable();
            $table->string('parent_phone', 20);
            $table->string('parent_locale', 2)->default('fr');
            $table->text('message')->nullable();
            $table->string('status', 20)->default('new')->index();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_requests');
    }
};
