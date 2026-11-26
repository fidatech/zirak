<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('type', 10)->default('percent'); // percent | fixed
            $table->unsignedInteger('value');
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_scholarship')->default(false);
            $table->string('reason')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 30)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->string('gateway', 20);
            $table->string('currency', 3);
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('discount')->default(0);
            $table->unsignedInteger('total');
            $table->string('gateway_session_id')->nullable()->index();
            $table->string('gateway_transaction_id')->nullable()->unique();
            $table->string('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('gateway_events', function (Blueprint $table) {
            $table->id();
            $table->string('gateway', 20);
            $table->string('reference')->nullable();
            $table->boolean('verified')->default(false);
            $table->json('payload');
            $table->string('result', 40)->nullable();
            $table->timestamps();
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('source', 20)->default('free'); // free | order | coupon | admin
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'course_id']);
        });

        Schema::create('lesson_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->timestamp('completed_at');
            $table->timestamps();
            $table->unique(['user_id', 'lesson_id']);
        });

        Schema::create('practice_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('chosen_index');
            $table->boolean('is_correct');
            $table->unsignedInteger('tries')->default(1);
            $table->timestamps();
            $table->unique(['user_id', 'question_id']);
        });

        Schema::create('test_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->json('answers');
            $table->unsignedTinyInteger('score');
            $table->boolean('passed');
            $table->timestamps();
            $table->index(['user_id', 'chapter_id']);
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->string('name_on_certificate');
            $table->string('type', 20);
            $table->unsignedTinyInteger('score')->nullable();
            $table->string('status', 20)->default('valid');
            $table->string('revoked_reason')->nullable();
            $table->timestamp('issued_at');
            $table->timestamps();
            $table->unique(['user_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('test_attempts');
        Schema::dropIfExists('practice_answers');
        Schema::dropIfExists('lesson_completions');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('gateway_events');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('coupons');
    }
};
