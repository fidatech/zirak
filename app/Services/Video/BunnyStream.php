<?php

namespace App\Services\Video;

use App\Models\Lesson;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Bunny Stream: teachers upload straight from the browser to Bunny with the TUS protocol (resumable),
 * using a short-lived signature from us; the library API key never leaves the server.
 */
class BunnyStream
{
    public const TUS_ENDPOINT = 'https://video.bunnycdn.com/tusupload';

    /** API video statuses: 0 created, 1 uploaded, 2 processing, 3 transcoding, 4 finished, 5 error, 6 upload failed. */
    public const READY = [4];

    public const FAILED = [5, 6];

    public function configured(): bool
    {
        return filled(config('services.bunny.library_id')) && filled(config('services.bunny.api_key'));
    }

    private function api(): PendingRequest
    {
        return Http::baseUrl('https://video.bunnycdn.com/library/'.config('services.bunny.library_id'))
            ->withHeaders(['AccessKey' => (string) config('services.bunny.api_key')])
            ->acceptJson()->timeout(20)->retry(2, 500, throw: false);
    }

    public function createVideo(string $title): string
    {
        $response = $this->api()->post('/videos', ['title' => mb_substr($title, 0, 200)]);
        if (! $response->successful() || blank($response->json('guid'))) {
            throw new RuntimeException('Bunny did not create the video: HTTP '.$response->status());
        }

        return $response->json('guid');
    }

    /** Headers for the browser's TUS upload. Valid for 24 hours; the signature only works for this one video. */
    public function uploadCredentials(string $videoId): array
    {
        $libraryId = (string) config('services.bunny.library_id');
        $expire = now()->addDay()->getTimestamp();

        return [
            'endpoint' => self::TUS_ENDPOINT,
            'headers' => [
                'AuthorizationSignature' => hash('sha256', $libraryId.config('services.bunny.api_key').$expire.$videoId),
                'AuthorizationExpire' => (string) $expire,
                'VideoId' => $videoId,
                'LibraryId' => $libraryId,
            ],
        ];
    }

    /** @return array{status:int, length:int, encodeProgress:int}|null */
    public function video(string $videoId): ?array
    {
        $response = $this->api()->get('/videos/'.$videoId);

        return $response->successful() ? $response->json() : null;
    }

    public function deleteVideo(string $videoId): void
    {
        try {
            $this->api()->delete('/videos/'.$videoId);
        } catch (Throwable $e) {
            report($e);
        }
    }

    /** Ask Bunny how the lesson's new video is doing and move it along. Webhooks and the sync command both call this. */
    public function refresh(Lesson $lesson): void
    {
        if (! $lesson->video_pending_id) {
            return;
        }
        $video = $this->video($lesson->video_pending_id);
        if ($video === null) {
            return;
        }
        $status = (int) ($video['status'] ?? 0);

        if (in_array($status, self::READY, true)) {
            $old = $lesson->video_id;
            $lesson->forceFill([
                'video_id' => $lesson->video_pending_id,
                'duration_seconds' => (int) ($video['length'] ?? 0) ?: $lesson->duration_seconds,
                'video_pending_id' => null, 'video_status' => null, 'video_progress' => null, 'video_status_at' => now(),
            ])->save();
            if ($old && $old !== $lesson->video_id) {
                $this->deleteVideo($old); // the replaced video
            }
            Log::info("Lesson {$lesson->id}: new video is ready.");
        } elseif (in_array($status, self::FAILED, true)) {
            $lesson->forceFill(['video_status' => 'failed', 'video_status_at' => now()])->save();
        } elseif ($status >= 1) {
            $lesson->forceFill(['video_status' => 'processing', 'video_progress' => (int) ($video['encodeProgress'] ?? 0), 'video_status_at' => now()])->save();
        }
    }
}
