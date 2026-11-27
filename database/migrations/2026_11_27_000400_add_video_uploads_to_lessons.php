<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A new video is uploaded into video_pending_id; students keep the current video_id until the new one is ready.
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('video_pending_id', 64)->nullable()->index()->after('video_id');
            $table->string('video_status', 20)->nullable()->after('video_pending_id'); // uploading | processing | failed
            $table->unsignedTinyInteger('video_progress')->nullable()->after('video_status'); // Bunny encode %
            $table->timestamp('video_status_at')->nullable()->after('video_progress');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', fn (Blueprint $t) => $t->dropColumn(['video_pending_id', 'video_status', 'video_progress', 'video_status_at']));
    }
};
