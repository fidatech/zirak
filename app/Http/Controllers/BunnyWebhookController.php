<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Services\Video\BunnyStream;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Bunny Stream calls this when a video's state changes. The request must be signed with the
 * library's read-only key (HMAC-SHA256 of the raw body); even then we only take the video ID
 * from it and ask Bunny's API for the real status.
 */
class BunnyWebhookController extends Controller
{
    public function __invoke(Request $request, BunnyStream $bunny): Response
    {
        $key = (string) config('services.bunny.read_only_key');
        $signature = (string) $request->header('X-BunnyStream-Signature');
        $valid = $key !== ''
            && $request->header('X-BunnyStream-Signature-Version') === 'v1'
            && strtolower((string) $request->header('X-BunnyStream-Signature-Algorithm')) === 'hmac-sha256'
            && hash_equals(hash_hmac('sha256', $request->getContent(), $key), strtolower($signature));
        abort_unless($valid, 401);

        $guid = (string) $request->json('VideoGuid');
        if ((string) $request->json('VideoLibraryId') === (string) config('services.bunny.library_id') && $guid !== '') {
            Lesson::where('video_pending_id', $guid)->get()->each(fn (Lesson $lesson) => $bunny->refresh($lesson));
        }

        return response('ok');
    }
}
