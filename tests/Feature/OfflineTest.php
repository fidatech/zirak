<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use App\Services\EnrollmentService;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflineTest extends TestCase
{
    use RefreshDatabase;

    private Course $course;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoContentSeeder::class);
        $this->course = Course::where('slug', 'ai-beginner')->firstOrFail();
        $this->user = User::factory()->create();
        config(['services.bunny.library_id' => '123']);
        $this->course->chapters->first()->lessons->first()->update(['video_id' => 'vid-abc']);
    }

    private function chapterUrl(string $tab = 'learn'): string
    {
        return route('chapters.show', [$this->course, $this->course->chapters->first(), $tab]);
    }

    public function test_enrolled_student_can_save_a_chapter_with_all_its_pages(): void
    {
        app(EnrollmentService::class)->enroll($this->user, $this->course, 'free');
        $chapter = $this->course->chapters->first();

        $page = $this->actingAs($this->user)->get($this->chapterUrl())->assertOk()->assertSee(__('app.offline.save'));
        $pack = $page->viewData('offline');

        $this->assertSame($this->course->slug.'/'.$chapter->id, $pack['key']);
        $this->assertContains('/courses/'.$this->course->slug, $pack['urls']);
        $this->assertContains(parse_url($this->chapterUrl('practice'), PHP_URL_PATH), $pack['urls']);
        $this->assertCount($chapter->lessons->count() + 3, $pack['urls']);
        $this->assertNotContains(parse_url($this->chapterUrl('test'), PHP_URL_PATH), $pack['urls']);

        // Every saved page really opens for this student.
        foreach ($pack['urls'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_preview_visitors_cannot_save_for_offline(): void
    {
        $chapter = $this->course->chapters->first();
        $chapter->lessons->first()->update(['is_preview' => true]);
        $this->actingAs($this->user)->get($this->chapterUrl())->assertOk()->assertDontSee(__('app.offline.save'));
    }

    public function test_video_waits_for_a_tap_when_data_saver_is_on(): void
    {
        app(EnrollmentService::class)->enroll($this->user, $this->course, 'free');
        $this->actingAs($this->user);

        $this->get($this->chapterUrl())->assertSee('loading="lazy"', false)->assertDontSee('data-video-saver', false);

        // Android data saver sends Save-Data: on.
        $this->get($this->chapterUrl(), ['Save-Data' => 'on'])->assertSee('data-video-saver', false)->assertSee(__('app.offline.play_video'));

        // Or the student turns it on in the profile.
        $this->put('/profile', ['locale' => 'prs', 'data_saver' => '1'])->assertRedirect();
        $this->assertTrue($this->user->fresh()->data_saver);
        $this->get($this->chapterUrl())->assertSee('data-video-saver', false);

        $this->put('/profile', ['locale' => 'prs']);
        $this->assertFalse($this->user->fresh()->data_saver);
    }

    public function test_logout_wipes_lessons_saved_on_the_phone(): void
    {
        $this->actingAs($this->user)->post('/logout')
            ->assertRedirect()
            ->assertHeader('Clear-Site-Data', '"cache", "storage"');
        $this->get('/profile')->assertRedirect();
    }

    public function test_test_tab_asks_for_internet_and_offline_page_lists_saved_chapters(): void
    {
        app(EnrollmentService::class)->enroll($this->user, $this->course, 'free');
        $this->actingAs($this->user);
        $chapter = $this->course->chapters->first();
        foreach ($chapter->lessons as $lesson) {
            $this->post(route('lessons.complete', [$this->course, $chapter, $lesson]));
        }
        foreach ($chapter->practiceQuestions as $q) {
            $this->postJson(route('practice.answer', [$this->course, $chapter]), ['question_id' => $q->id, 'choice' => $q->correct_index]);
        }
        $this->get($this->chapterUrl('test'))->assertSee('data-needs-internet', false)->assertSee(__('app.offline.test_offline'));

        $this->get('/offline')->assertOk()->assertSee(__('app.offline.saved_title'));
        $this->get('/profile')->assertSee(__('app.offline.saved_title'))->assertSee(__('app.offline.data_saver'));
    }

    public function test_answers_synced_late_are_still_checked_by_the_server(): void
    {
        // An answer queued offline arrives later as JSON; the server checks it like any other.
        app(EnrollmentService::class)->enroll($this->user, $this->course, 'free');
        $chapter = $this->course->chapters->first();
        $q = $chapter->practiceQuestions->first();

        $page = $this->actingAs($this->user)->get($this->chapterUrl('practice'));
        $this->assertStringNotContainsString('correct_index', json_encode($page->viewData('practice')));

        $this->postJson(route('practice.answer', [$this->course, $chapter]), ['question_id' => $q->id, 'choice' => $q->correct_index])
            ->assertOk()->assertJson(['correct' => true]);
        $this->postJson(route('lessons.complete', [$this->course, $chapter, $chapter->lessons->first()]))->assertRedirect();
        $this->assertDatabaseHas('lesson_completions', ['user_id' => $this->user->id, 'lesson_id' => $chapter->lessons->first()->id]);
    }
}
