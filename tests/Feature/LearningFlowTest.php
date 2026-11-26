<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoContentSeeder::class);
        $this->user = User::factory()->create(['name' => 'Maryam Ahmadi']);
        $this->course = Course::where('slug', 'ai-beginner')->firstOrFail();
    }

    public function test_full_course_learn_practice_test_certificate(): void
    {
        $this->actingAs($this->user);

        // Free course: enroll.
        $this->post(route('courses.purchase', $this->course))->assertRedirect(route('courses.show', $this->course));
        $this->assertTrue($this->user->isEnrolledIn($this->course));
        $this->get(route('courses.show', $this->course))->assertOk()->assertSee($this->course->chapters->first()->title);

        foreach ($this->course->chapters as $chapter) {
            // Test is locked before learning and practice.
            $this->get(route('chapters.show', [$this->course, $chapter, 'test']))->assertOk()->assertSee(__('app.chapter.test_locked'));
            $this->post(route('test.submit', [$this->course, $chapter]), ['answers' => []])->assertForbidden();

            foreach ($chapter->lessons as $lesson) {
                $this->get(route('chapters.show', [$this->course, $chapter, 'learn', 'lesson' => $lesson->id]))->assertOk()->assertSee($lesson->title);
                $this->post(route('lessons.complete', [$this->course, $chapter, $lesson]))->assertRedirect();
            }

            $this->get(route('chapters.show', [$this->course, $chapter, 'practice']))->assertOk();
            foreach ($chapter->practiceQuestions as $q) {
                $wrong = ($q->correct_index + 1) % count($q->options);
                $this->postJson(route('practice.answer', [$this->course, $chapter]), ['question_id' => $q->id, 'choice' => $wrong])
                    ->assertOk()->assertJson(['correct' => false, 'correct_index' => $q->correct_index]);
                $this->postJson(route('practice.answer', [$this->course, $chapter]), ['question_id' => $q->id, 'choice' => $q->correct_index])
                    ->assertOk()->assertJson(['correct' => true]);
            }

            // Failing attempt first, then a passing one.
            $tests = $chapter->testQuestions;
            $wrong = $tests->mapWithKeys(fn ($q) => [$q->id => ($q->correct_index + 1) % count($q->options)])->all();
            $this->post(route('test.submit', [$this->course, $chapter]), ['answers' => $wrong])->assertRedirect();
            $this->assertDatabaseHas('test_attempts', ['chapter_id' => $chapter->id, 'score' => 0, 'passed' => false]);

            $right = $tests->mapWithKeys(fn ($q) => [$q->id => $q->correct_index])->all();
            $this->post(route('test.submit', [$this->course, $chapter]), ['answers' => $right])->assertRedirect();
            $this->assertDatabaseHas('test_attempts', ['chapter_id' => $chapter->id, 'score' => 100, 'passed' => true]);
        }

        $certificate = Certificate::where('user_id', $this->user->id)->where('course_id', $this->course->id)->firstOrFail();
        $this->assertSame('Maryam Ahmadi', $certificate->name_on_certificate);
        $this->assertSame(100, $certificate->score);
        $this->assertNotNull($this->user->enrollments()->first()->completed_at);

        $this->get(route('certificates.show', $certificate))->assertOk()->assertSee('Maryam Ahmadi');
        $this->get(route('report'))->assertOk();
        $this->get(route('home'))->assertOk();

        auth()->logout();
        $this->get(route('certificates.verify', $certificate->code))->assertOk()->assertSee(__('app.cert.valid'))->assertSee('Maryam Ahmadi');
        $this->get(route('certificates.verify', strtolower($certificate->code)))->assertOk()->assertSee(__('app.cert.valid'));
        $this->get(route('certificates.verify', 'ZRK-00-NOPE'))->assertOk()->assertSee(__('app.cert.not_found'));

        $certificate->update(['status' => 'revoked']);
        $this->get(route('certificates.verify', $certificate->code))->assertSee(__('app.cert.revoked'));
    }

    public function test_paid_course_content_is_locked_until_bought(): void
    {
        $course = Course::where('slug', 'mobile-filmmaking')->firstOrFail();
        $chapter = $course->chapters->first();
        $previewLesson = $chapter->lessons->firstWhere('is_preview', true);

        $this->actingAs($this->user);
        $this->get(route('chapters.show', [$course, $chapter, 'learn', 'lesson' => $previewLesson->id]))->assertOk()->assertSee($previewLesson->title);
        $this->get(route('chapters.show', [$course, $chapter, 'practice']))->assertForbidden();
        $this->post(route('lessons.complete', [$course, $chapter, $previewLesson]))->assertForbidden();
        $this->postJson(route('practice.answer', [$course, $chapter]), ['question_id' => 1, 'choice' => 0])->assertForbidden();

        $chapter2 = $course->chapters->last();
        $this->get(route('chapters.show', [$course, $chapter2, 'learn']))->assertForbidden();
    }

    public function test_chapter_must_belong_to_course(): void
    {
        $other = Course::where('slug', 'mobile-filmmaking')->firstOrFail()->chapters->first();
        $this->actingAs($this->user)->get('/courses/ai-beginner/chapters/'.$other->id)->assertNotFound();
    }

    public function test_student_cannot_see_someone_elses_certificate(): void
    {
        $owner = User::factory()->create();
        $cert = Certificate::create(['code' => 'ZRK-27-AAAAAA', 'user_id' => $owner->id, 'course_id' => $this->course->id, 'name_on_certificate' => 'X', 'type' => 'achievement', 'issued_at' => now()]);
        $this->actingAs($this->user)->get(route('certificates.show', $cert))->assertForbidden();
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get(route('report'))->assertRedirect(route('login'));
        $this->post(route('courses.purchase', $this->course))->assertRedirect(route('login'));
    }
}
