<?php

namespace App\Http\Controllers;

use App\Enums\QuestionKind;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\PracticeAnswer;
use App\Models\Question;
use App\Models\TestAttempt;
use App\Services\CertificateIssuer;
use App\Services\ProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChapterController extends Controller
{
    public function __construct(private ProgressService $progress, private CertificateIssuer $certificates) {}

    public function show(Request $request, Course $course, Chapter $chapter, string $tab = 'learn'): View
    {
        abort_unless(in_array($tab, ['learn', 'practice', 'test'], true), 404);
        $user = $request->user();
        $enrolled = $user->isEnrolledIn($course);

        $chapter->load('lessons');
        $lessons = $chapter->lessons;

        if (! $enrolled) {
            // Not bought yet: only free preview lessons in the Learn tab.
            abort_unless($tab === 'learn' && $lessons->contains('is_preview', true), 403);
            $lessons = $lessons->where('is_preview', true)->values();
        }

        $completed = LessonCompletion::where('user_id', $user->id)->whereIn('lesson_id', $lessons->pluck('id'))->pluck('lesson_id')->all();
        $lesson = $lessons->firstWhere('id', (int) $request->query('lesson'))
            ?? $lessons->first(fn (Lesson $l) => ! in_array($l->id, $completed, true))
            ?? $lessons->first();

        $state = $this->progress->chapterState($user, $chapter);
        $chapters = $course->chapters()->get();

        $data = compact('course', 'chapter', 'tab', 'lessons', 'lesson', 'completed', 'state', 'enrolled', 'chapters');
        $data['offline'] = $enrolled ? $this->offlinePack($course, $chapter, $lessons) : null;

        if ($tab === 'practice') {
            $questions = $chapter->practiceQuestions()->get();
            $answers = PracticeAnswer::where('user_id', $user->id)->whereIn('question_id', $questions->pluck('id'))->get()->keyBy('question_id');
            $data['practice'] = $questions->map(fn (Question $q) => [
                'id' => $q->id,
                'body' => $q->body,
                'options' => $q->options,
                'answered' => $answers->has($q->id),
            ])->values();
            $data['startIndex'] = max(0, $questions->search(fn ($q) => ! $answers->has($q->id)) ?: 0);
            if ($answers->count() >= $questions->count()) {
                $data['startIndex'] = $questions->count();
            }
        }

        if ($tab === 'test') {
            $data['questions'] = $chapter->testQuestions()->get();
            $data['lastAttempt'] = TestAttempt::where('user_id', $user->id)->where('chapter_id', $chapter->id)->latest('id')->first();
        }

        return view('chapters.show', $data);
    }

    /** Pages a student can save on the phone to study this chapter without internet. */
    private function offlinePack(Course $course, Chapter $chapter, $lessons): array
    {
        $path = fn (string $url) => parse_url($url, PHP_URL_PATH).(($q = parse_url($url, PHP_URL_QUERY)) ? '?'.$q : '');

        return [
            'key' => $course->slug.'/'.$chapter->id,
            'title' => $chapter->title,
            'course' => $course->title,
            'url' => $path(route('chapters.show', [$course, $chapter])),
            'urls' => collect([route('courses.show', $course), route('chapters.show', [$course, $chapter])])
                ->merge($lessons->map(fn (Lesson $l) => route('chapters.show', [$course, $chapter, 'learn', 'lesson' => $l->id])))
                ->push(route('chapters.show', [$course, $chapter, 'practice']))
                ->map($path)->values()->all(),
        ];
    }

    public function completeLesson(Request $request, Course $course, Chapter $chapter, Lesson $lesson): RedirectResponse
    {
        abort_unless($lesson->chapter_id === $chapter->id && $request->user()->isEnrolledIn($course), 403);

        LessonCompletion::firstOrCreate(
            ['user_id' => $request->user()->id, 'lesson_id' => $lesson->id],
            ['completed_at' => now()],
        );

        $next = $chapter->lessons()->where('sort', '>', $lesson->sort)->first()
            ?? $chapter->lessons()->where('id', '>', $lesson->id)->where('sort', $lesson->sort)->first();

        return $next
            ? redirect()->route('chapters.show', [$course, $chapter, 'learn', 'lesson' => $next->id])
            : redirect()->route('chapters.show', [$course, $chapter, 'practice']);
    }

    public function answer(Request $request, Course $course, Chapter $chapter): JsonResponse
    {
        abort_unless($request->user()->isEnrolledIn($course), 403);
        $data = $request->validate(['question_id' => ['required', 'integer'], 'choice' => ['required', 'integer', 'min:0', 'max:9']]);

        $question = $chapter->questions()->where('kind', QuestionKind::Practice)->findOrFail($data['question_id']);
        $correct = (int) $data['choice'] === (int) $question->correct_index;

        $answer = PracticeAnswer::firstOrNew(['user_id' => $request->user()->id, 'question_id' => $question->id]);
        $answer->tries = $answer->exists ? $answer->tries + 1 : 1;
        $answer->fill(['chosen_index' => $data['choice'], 'is_correct' => $correct])->save();

        return response()->json([
            'correct' => $correct,
            'correct_index' => $question->correct_index,
            'explanation' => $question->explanation,
        ]);
    }

    public function submitTest(Request $request, Course $course, Chapter $chapter): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isEnrolledIn($course), 403);

        $state = $this->progress->chapterState($user, $chapter);
        abort_unless($state['test_unlocked'], 403);

        $questions = $chapter->testQuestions()->get();
        abort_if($questions->isEmpty(), 404);

        $answers = (array) $request->input('answers', []);
        $right = $questions->filter(fn (Question $q) => isset($answers[$q->id]) && (int) $answers[$q->id] === (int) $q->correct_index)->count();
        $score = (int) round($right / $questions->count() * 100);

        TestAttempt::create([
            'user_id' => $user->id,
            'chapter_id' => $chapter->id,
            'answers' => collect($answers)->only($questions->pluck('id')->all())->map(fn ($v) => (int) $v)->all(),
            'score' => $score,
            'passed' => $score >= $this->progress->passMark(),
        ]);

        if ($this->certificates->maybeIssue($user, $course)) {
            session()->flash('status', __('app.course.certificate_ready'));
        }

        return redirect()->route('chapters.show', [$course, $chapter, 'test']);
    }
}
