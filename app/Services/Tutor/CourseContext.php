<?php

namespace App\Services\Tutor;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Support\Collection;

/**
 * Picks the parts of a course that matter for a question, without a vector database:
 * the current lesson first, then other lessons ranked by shared words.
 */
class CourseContext
{
    public function build(Course $course, ?Lesson $current, string $question, int $maxChars): string
    {
        $course->loadMissing('chapters.lessons', 'chapters.practiceQuestions');

        $parts = ['# '.$course->title];
        if ($course->subtitle) {
            $parts[] = $course->subtitle;
        }
        if ($course->outcomes) {
            $parts[] = 'Outcomes: '.implode(' · ', $course->outcomes);
        }

        $lessons = $course->chapters->flatMap(fn ($chapter) => $chapter->lessons->map(fn ($l) => $l->setRelation('chapter', $chapter)));

        if ($current) {
            $parts[] = $this->lessonText($lessons->firstWhere('id', $current->id) ?? $current, 'CURRENT LESSON');
            $chapter = $course->chapters->firstWhere('id', $current->chapter_id);
            $explanations = $chapter?->practiceQuestions->filter(fn ($q) => filled($q->explanation))
                ->map(fn ($q) => '- '.$q->body.' → '.$q->explanation)->implode("\n");
            if ($explanations) {
                $parts[] = "## Practice explanations (this chapter)\n".$explanations;
            }
        }

        $used = mb_strlen(implode("\n\n", $parts));
        foreach ($this->rank($lessons->reject(fn ($l) => $l->id === $current?->id), $question) as $lesson) {
            $text = $this->lessonText($lesson, 'Lesson');
            if ($used + mb_strlen($text) > $maxChars) {
                continue;
            }
            $parts[] = $text;
            $used += mb_strlen($text);
        }

        return mb_substr(implode("\n\n", $parts), 0, $maxChars);
    }

    private function lessonText(Lesson $lesson, string $label): string
    {
        return trim(implode("\n", array_filter([
            "## {$label}: ".($lesson->chapter ? $lesson->chapter->title.' — ' : '').$lesson->title,
            $lesson->body,
            $lesson->prompt ? "Prompt shown to students:\n".$lesson->prompt : null,
            $lesson->task ? 'Student task: '.$lesson->task : null,
        ])));
    }

    /** @return Collection<int, Lesson> */
    private function rank(Collection $lessons, string $question): Collection
    {
        $words = $this->words($question);

        return $lessons
            ->map(fn (Lesson $l) => ['lesson' => $l, 'score' => count(array_intersect($words, $this->words($l->title.' '.$l->title.' '.$l->body.' '.$l->prompt)))])
            ->filter(fn ($row) => $row['score'] > 0)
            ->sortByDesc('score')
            ->pluck('lesson')
            ->values();
    }

    /** @return array<int, string> */
    public function words(string $text): array
    {
        // Unify Arabic/Persian letter forms and drop diacritics and zero-width joiners.
        $text = str_replace(['ي', 'ى', 'ك', 'ۀ', 'ة', "\u{200C}", "\u{200D}"], ['ی', 'ی', 'ک', 'ه', 'ه', ' ', ''], mb_strtolower($text));
        $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $text);
        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_unique(array_filter($tokens, fn ($t) => mb_strlen($t) >= 3)));
    }
}
