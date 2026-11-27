<?php

namespace App\Console\Commands;

use App\Models\Lesson;
use App\Services\Video\BunnyStream;
use Illuminate\Console\Command;

/** Catches up on videos whose webhook was missed (or when no webhook is set up). Runs every 5 minutes. */
class VideoSync extends Command
{
    protected $signature = 'zirak:video-sync';

    protected $description = 'Check Bunny for lesson videos that are still being uploaded or processed';

    public function handle(BunnyStream $bunny): int
    {
        if (! $bunny->configured()) {
            return self::SUCCESS;
        }
        $lessons = Lesson::whereNotNull('video_pending_id')->whereIn('video_status', ['uploading', 'processing'])->limit(50)->get();
        foreach ($lessons as $lesson) {
            $bunny->refresh($lesson);
        }
        $this->info('Checked '.$lessons->count().' videos.');

        return self::SUCCESS;
    }
}
