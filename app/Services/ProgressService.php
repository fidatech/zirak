<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\LessonCompletion;
use App\Models\PracticeAnswer;
use App\Models\TestAttempt;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Works out where a student is in a course: Learn -> Practice -> Test per chapter.
 */
class ProgressService
{
    public function passMark(): int
    {
        return (int) config('zirak.pass_mark', 70);
    }

    /**
     * @return array{lessons_total:int,lessons_done:int,learn_done:bool,practice_total:int,practice_answered:int,practice_correct:int,practice_done:bool,test_total:int,test_unlocked:bool,best_score:?int,passed:bool,complete:bool}
     */
    public function chapterState(User $user, Chapter $chapter): array
    {
        $lessonIds = $chapter->lessons()->pluck('id');
        $lessonsDone = LessonCompletion::where('user_id', $user->id)->whereIn('lesson_id', $lessonIds)->count();

        $practiceIds = $chapter->practiceQuestions()->pluck('id');
        $answers = PracticeAnswer::where('user_id', $user->id)->whereIn('question_id', $practiceIds)->get();

        $testTotal = $chapter->testQuestions()->count();
        $best = TestAttempt::where('user_id', $user->id)->where('chapter_id', $chapter->id)->max('score');

        $learnDone = $lessonIds->count() === 0 || $lessonsDone >= $lessonIds->count();
        $practiceDone = $practiceIds->count() === 0 || $answers->count() >= $practiceIds->count();
        $passed = $testTotal === 0 ? ($learnDone && $practiceDone) : ($best !== null && $best >= $this->passMark());

        return [
            'lessons_total' => $lessonIds->count(),
            'lessons_done' => $lessonsDone,
            'learn_done' => $learnDone,
            'practice_total' => $practiceIds->count(),
            'practice_answered' => $answers->count(),
            'practice_correct' => $answers->where('is_correct', true)->count(),
            'practice_done' => $practiceDone,
            'test_total' => $testTotal,
            'test_unlocked' => $learnDone && $practiceDone,
            'best_score' => $best !== null ? (int) $best : null,
            'passed' => $passed,
            'complete' => $learnDone && $practiceDone && $passed,
        ];
    }

    /**
     * @return array{chapters:Collection,percent:int,complete:bool,average_score:?int,current:?Chapter,next:?array}
     */
    public function courseState(User $user, Course $course): array
    {
        $chapters = $course->chapters()->get();
        $states = $chapters->mapWithKeys(fn (Chapter $c) => [$c->id => $this->chapterState($user, $c)]);

        $steps = 0;
        $done = 0;
        foreach ($states as $s) {
            $steps += 3;
            $done += (int) $s['learn_done'] + (int) $s['practice_done'] + (int) $s['passed'];
        }

        $current = $chapters->first(fn (Chapter $c) => ! $states[$c->id]['complete']);
        $scores = $states->pluck('best_score')->filter(fn ($v) => $v !== null);

        return [
            'chapters' => $states,
            'percent' => $steps === 0 ? 0 : (int) round($done / $steps * 100),
            'complete' => $chapters->isNotEmpty() && $current === null,
            'average_score' => $scores->isEmpty() ? null : (int) round($scores->avg()),
            'current' => $current,
            'next' => $current ? $this->nextStep($course, $current, $states[$current->id]) : null,
        ];
    }

    /** @return array{tab:string,chapter:Chapter,course:Course} */
    public function nextStep(Course $course, Chapter $chapter, array $state): array
    {
        $tab = ! $state['learn_done'] ? 'learn' : (! $state['practice_done'] ? 'practice' : 'test');

        return ['tab' => $tab, 'chapter' => $chapter, 'course' => $course];
    }

    /**
     * Chapters where the student is weak (practice under 70% correct or best test below pass mark).
     *
     * @return Collection<int, array{chapter:Chapter,percent:int}>
     */
    public function weakTopics(User $user, int $limit = 5): Collection
    {
        $answers = PracticeAnswer::where('user_id', $user->id)->with('question.chapter.course')->get();

        return $answers->groupBy(fn ($a) => $a->question->chapter_id)
            ->map(function ($group) {
                $chapter = $group->first()->question->chapter;

                return ['chapter' => $chapter, 'percent' => (int) round($group->where('is_correct', true)->count() / $group->count() * 100)];
            })
            ->sortBy('percent')
            ->take($limit)
            ->values();
    }

    /**
     * Activity for the last 7 days.
     *
     * @return array{days:array<int,array{date:CarbonImmutable,minutes:int}>,minutes:int,lessons:int,average_score:?int,tests:int,streak:int}
     */
    public function weeklyReport(User $user): array
    {
        $start = CarbonImmutable::today()->subDays(6);

        $completions = LessonCompletion::where('user_id', $user->id)
            ->where('completed_at', '>=', $start)->with('lesson:id,duration_seconds')->get();
        $practice = PracticeAnswer::where('user_id', $user->id)->where('updated_at', '>=', $start)->get();
        $tests = TestAttempt::where('user_id', $user->id)->where('created_at', '>=', $start)->get();

        $days = [];
        $total = 0;
        for ($i = 0; $i < 7; $i++) {
            $date = $start->addDays($i);
            $seconds = $completions->filter(fn ($c) => $c->completed_at->isSameDay($date))->sum(fn ($c) => $c->lesson?->duration_seconds ?? 0);
            $seconds += $practice->filter(fn ($p) => $p->updated_at->isSameDay($date))->count() * 60;
            $seconds += $tests->filter(fn ($t) => $t->created_at->isSameDay($date))->count() * 300;
            $minutes = (int) round($seconds / 60);
            $total += $minutes;
            $days[] = ['date' => $date, 'minutes' => $minutes];
        }

        return [
            'days' => $days,
            'minutes' => $total,
            'lessons' => $completions->count(),
            'tests' => $tests->count(),
            'average_score' => $tests->isEmpty() ? null : (int) round($tests->avg('score')),
            'streak' => $this->streak($user),
        ];
    }

    public function streak(User $user): int
    {
        $dates = LessonCompletion::where('user_id', $user->id)->pluck('completed_at')
            ->merge(PracticeAnswer::where('user_id', $user->id)->pluck('updated_at'))
            ->merge(TestAttempt::where('user_id', $user->id)->pluck('created_at'))
            ->map(fn ($d) => CarbonImmutable::parse($d)->toDateString())
            ->unique()->flip();

        $day = CarbonImmutable::today();
        if (! isset($dates[$day->toDateString()])) {
            $day = $day->subDay();
        }

        $streak = 0;
        while (isset($dates[$day->toDateString()])) {
            $streak++;
            $day = $day->subDay();
        }

        return $streak;
    }
}
