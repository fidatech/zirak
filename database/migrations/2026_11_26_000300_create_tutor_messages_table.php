<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tutor_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('question_id')->nullable()->constrained('tutor_messages')->cascadeOnDelete(); // answers point to the student's question
            $table->string('role', 10); // student | ai | teacher
            $table->text('content');
            $table->string('locale', 5)->default('prs');
            $table->unsignedInteger('tokens_in')->default(0);
            $table->unsignedInteger('tokens_out')->default(0);
            $table->boolean('helpful')->nullable();
            $table->timestamp('escalated_at')->nullable()->index();
            $table->timestamp('answered_at')->nullable();
            $table->foreignId('answered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['user_id', 'course_id']);
            $table->index(['user_id', 'role', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutor_messages');
    }
};
