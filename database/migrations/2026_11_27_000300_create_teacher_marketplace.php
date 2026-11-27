<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('teacher_headline', 120)->nullable()->after('role');
            $table->text('teacher_bio')->nullable()->after('teacher_headline');
        });

        Schema::create('teacher_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('full_name', 120);
            $table->string('headline', 120);
            $table->text('about');
            $table->text('course_idea');
            $table->json('languages');
            $table->string('sample_url', 500)->nullable();
            $table->string('status', 20)->default('pending')->index(); // pending | approved | rejected
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('teacher_id')->nullable()->after('category_id')->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('teacher_share')->nullable()->after('teacher_id'); // percent; null = config default
            $table->timestamp('submitted_at')->nullable()->after('published_at');
            $table->text('review_note')->nullable()->after('submitted_at');
        });

        // What the teacher earned, fixed at the moment of payment.
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('teacher_id')->nullable()->after('course_id')->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('teacher_share')->nullable()->after('teacher_id');
            $table->unsignedInteger('teacher_amount')->default(0)->after('teacher_share');
        });

        Schema::create('teacher_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->restrictOnDelete();
            $table->string('currency', 3);
            $table->unsignedInteger('amount');
            $table->string('method', 40); // hesabpay | bank | cash | other
            $table->string('reference', 120)->nullable();
            $table->string('note', 500)->nullable();
            $table->date('paid_on');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_payouts');
        Schema::table('orders', fn (Blueprint $t) => $t->dropConstrainedForeignId('teacher_id'));
        Schema::table('orders', fn (Blueprint $t) => $t->dropColumn(['teacher_share', 'teacher_amount']));
        Schema::table('courses', fn (Blueprint $t) => $t->dropConstrainedForeignId('teacher_id'));
        Schema::table('courses', fn (Blueprint $t) => $t->dropColumn(['teacher_share', 'submitted_at', 'review_note']));
        Schema::dropIfExists('teacher_applications');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['teacher_headline', 'teacher_bio']));
    }
};
