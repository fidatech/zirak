<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('telegram_id')->nullable()->unique()->after('phone');
            $table->string('telegram_username', 64)->nullable()->after('telegram_id');
            $table->timestamp('telegram_linked_at')->nullable()->after('telegram_username');
            $table->boolean('telegram_reminders')->default(true)->after('telegram_linked_at');
            $table->timestamp('telegram_reminded_at')->nullable()->after('telegram_reminders');
        });

        Schema::create('telegram_logins', function (Blueprint $table) {
            $table->id();
            $table->string('token_hash', 64)->unique();
            $table->string('purpose', 10); // login | link
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete(); // for "link"
            $table->string('status', 12)->default('pending'); // pending | confirmed | used | cancelled
            $table->unsignedBigInteger('telegram_id')->nullable();
            $table->string('telegram_username', 64)->nullable();
            $table->string('telegram_name', 120)->nullable();
            $table->string('telegram_language', 10)->nullable();
            $table->string('device', 120)->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_logins');
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['telegram_id']);
            $table->dropColumn(['telegram_id', 'telegram_username', 'telegram_linked_at', 'telegram_reminders', 'telegram_reminded_at']);
        });
    }
};
