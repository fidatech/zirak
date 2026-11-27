<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Lesson;
use App\Services\Video\BunnyStream;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/** Upload a lesson video straight to Bunny. Only the course's teacher or an admin. */
class VideoUploadController extends Controller
{
    private function authorizeLesson(Request $request, Lesson $lesson): void
    {
        $user = $request->user();
        $teacherId = $lesson->chapter?->course?->teacher_id;
        abort_unless($user->role === UserRole::Admin || ($user->isTeacher() && $teacherId === $user->id), 404);
    }

    public function start(Request $request, Lesson $lesson, BunnyStream $bunny): JsonResponse
    {
        $this->authorizeLesson($request, $lesson);
        abort_unless($bunny->configured(), 503, __('teach.video.not_configured'));

        // Same lesson, upload started recently: resume the same Bunny video instead of making a new one.
        $resumable = $lesson->video_pending_id && $lesson->video_status === 'uploading'
            && $lesson->video_status_at?->gt(now()->subHours(20));

        if (! $resumable) {
            if ($lesson->video_pending_id) {
                $bunny->deleteVideo($lesson->video_pending_id); // an unfinished or failed earlier upload
            }
            try {
                $guid = $bunny->createVideo($lesson->chapter->course->title.' — '.$lesson->title);
            } catch (Throwable $e) {
                report($e);
                abort(502, __('teach.video.bunny_error'));
            }
            $lesson->forceFill(['video_pending_id' => $guid, 'video_status' => 'uploading', 'video_progress' => null, 'video_status_at' => now()])->save();
        }

        return response()->json([...$bunny->uploadCredentials($lesson->video_pending_id), 'title' => $lesson->title]);
    }

    /** The browser finished sending the file; Bunny now processes it. */
    public function finished(Request $request, Lesson $lesson, BunnyStream $bunny): JsonResponse
    {
        $this->authorizeLesson($request, $lesson);
        if ($lesson->video_status === 'uploading') {
            $lesson->forceFill(['video_status' => 'processing', 'video_status_at' => now()])->save();
        }
        $bunny->refresh($lesson);

        return $this->status($request, $lesson->fresh());
    }

    public function status(Request $request, Lesson $lesson): JsonResponse
    {
        $this->authorizeLesson($request, $lesson);

        return response()->json([
            'status' => $lesson->video_status ?? ($lesson->video_id ? 'ready' : 'none'),
            'progress' => $lesson->video_progress,
            'has_video' => (bool) $lesson->video_id,
        ]);
    }
}
