<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\TutorMessage;
use App\Services\Tutor\TutorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TutorController extends Controller
{
    public function __construct(private TutorService $tutor) {}

    public function history(Request $request, Course $course): JsonResponse
    {
        abort_unless($request->user()->isEnrolledIn($course), 403);

        $messages = TutorMessage::where('user_id', $request->user()->id)->where('course_id', $course->id)
            ->latest('id')->take(30)->get()->reverse()->values();

        return response()->json([
            'available' => $this->tutor->available(),
            'remaining' => $this->tutor->remainingToday($request->user()),
            'messages' => $messages->map->toChat(),
        ]);
    }

    public function ask(Request $request, Course $course): JsonResponse
    {
        abort_unless($request->user()->isEnrolledIn($course), 403);
        abort_unless($this->tutor->available(), 503);

        $data = $request->validate([
            'question' => ['required', 'string', 'min:2', 'max:1000'],
            'lesson_id' => ['nullable', 'integer'],
        ]);

        $lesson = isset($data['lesson_id'])
            ? Lesson::whereKey($data['lesson_id'])->whereHas('chapter', fn ($q) => $q->where('course_id', $course->id))->first()
            : null;

        [$question, $answer] = $this->tutor->ask($request->user(), $course, $lesson, trim($data['question']));

        return response()->json([
            'messages' => [$question->toChat(), $answer->toChat()],
            'remaining' => $this->tutor->remainingToday($request->user()),
        ]);
    }

    public function escalate(Request $request, TutorMessage $message): JsonResponse
    {
        abort_unless($message->user_id === $request->user()->id, 403);
        $question = $message->role === 'student' ? $message : $message->question;
        abort_unless($question && $question->role === 'student', 422);

        $this->tutor->escalate($question);

        return response()->json(['ok' => true, 'question_id' => $question->id]);
    }

    public function rate(Request $request, TutorMessage $message): JsonResponse
    {
        abort_unless($message->user_id === $request->user()->id && $message->role === 'ai', 403);
        $message->update(['helpful' => $request->boolean('helpful')]);

        return response()->json(['ok' => true]);
    }
}
