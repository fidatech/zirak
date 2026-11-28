<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // An order is either one course or one month of Zirak Plus.
        Schema::table('orders', function (Blueprint $table) {
            $table->string('kind', 20)->default('course')->after('number')->index(); // course | subscription
            $table->foreignId('course_id')->nullable()->change();
        });

        // One row per paid period. Paying again adds a new period after the current one.
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->index();
            $table->timestamp('reminded_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'ends_at']);
        });

        // Each month the teachers' share of plan money is split by lessons subscribers completed in their courses.
        Schema::create('teacher_pool_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('month', 7); // 2026-11
            $table->string('currency', 3);
            $table->unsignedInteger('lessons');
            $table->unsignedInteger('total_lessons');
            $table->unsignedInteger('pool');
            $table->unsignedInteger('amount');
            $table->timestamps();
            $table->unique(['teacher_id', 'month', 'currency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_pool_earnings');
        Schema::dropIfExists('subscriptions');
        Schema::table('orders', fn (Blueprint $t) => $t->dropColumn('kind'));
    }
};
