<?php

namespace Tests\Feature;

use App\Enums\CourseStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Services\EnrollmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VideoUploadTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private Lesson $lesson;

    private array $bunnyStatus = ['status' => 2, 'length' => 0, 'encodeProgress' => 40];

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
        config(['services.bunny.library_id' => '4242', 'services.bunny.api_key' => 'lib-key', 'services.bunny.read_only_key' => 'ro-key']);
        Http::fake([
            'video.bunnycdn.com/library/4242/videos' => Http::sequence()
                ->push(['guid' => 'guid-1', 'title' => 'x'])
                ->push(['guid' => 'guid-2', 'title' => 'x'])
                ->push(['guid' => 'guid-3', 'title' => 'x']),
            'video.bunnycdn.com/library/4242/videos/*' => fn ($request) => $request->method() === 'DELETE'
                ? Http::response(['success' => true])
                : Http::response($this->bunnyStatus),
        ]);

        $this->teacher = User::factory()->create(['role' => UserRole::Instructor, 'locale' => 'en']);
        $category = Category::create(['slug' => 'film', 'name_prs' => 'فلم', 'name_ps' => 'فلم', 'name_en' => 'Film', 'color' => 'teal', 'icon' => 'pencil']);
        $course = Course::create(['category_id' => $category->id, 'teacher_id' => $this->teacher->id, 'slug' => 'film', 'title' => 'Film', 'status' => CourseStatus::Published, 'price_afn' => 0, 'is_free' => true, 'language' => 'prs']);
        $chapter = $course->chapters()->create(['title' => 'One', 'sort' => 1]);
        $this->lesson = $chapter->lessons()->create(['title' => 'Light', 'body' => 'Text', 'sort' => 1, 'video_id' => 'old-video']);
    }

    private function signed(array $payload): array
    {
        $body = json_encode($payload);

        return [$body, [
            'X-BunnyStream-Signature' => hash_hmac('sha256', $body, 'ro-key'),
            'X-BunnyStream-Signature-Version' => 'v1',
            'X-BunnyStream-Signature-Algorithm' => 'hmac-sha256',
            'CONTENT_TYPE' => 'application/json',
        ]];
    }

    public function test_teacher_gets_signed_upload_details_without_the_api_key(): void
    {
        $response = $this->actingAs($this->teacher)->postJson(route('video.start', $this->lesson))->assertOk();
        $expire = $response->json('headers.AuthorizationExpire');

        $response->assertJsonPath('endpoint', 'https://video.bunnycdn.com/tusupload')
            ->assertJsonPath('headers.VideoId', 'guid-1')
            ->assertJsonPath('headers.LibraryId', '4242')
            ->assertJsonPath('headers.AuthorizationSignature', hash('sha256', '4242lib-key'.$expire.'guid-1'));
        $this->assertStringNotContainsString('lib-key', $response->getContent());
        Http::assertSent(fn ($r) => $r->method() === 'POST' && $r->hasHeader('AccessKey', 'lib-key') && $r['title'] === 'Film — Light');

        // Students still see the old video while the new one uploads.
        $this->lesson->refresh();
        $this->assertSame('old-video', $this->lesson->video_id);
        $this->assertSame('guid-1', $this->lesson->video_pending_id);
        $this->assertSame('uploading', $this->lesson->video_status);

        // Asking again (resume after a dropped connection) reuses the same Bunny video.
        $this->postJson(route('video.start', $this->lesson))->assertJsonPath('headers.VideoId', 'guid-1');
    }

    public function test_only_the_course_teacher_or_an_admin_can_upload(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Instructor]))->postJson(route('video.start', $this->lesson))->assertNotFound();
        $this->actingAs(User::factory()->create())->postJson(route('video.start', $this->lesson))->assertNotFound();
        $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))->postJson(route('video.start', $this->lesson))->assertOk();
        $this->post('/logout');
        $this->postJson(route('video.start', $this->lesson))->assertUnauthorized();
    }

    public function test_finished_upload_becomes_the_lesson_video_when_bunny_is_done(): void
    {
        $this->actingAs($this->teacher)->postJson(route('video.start', $this->lesson));

        $this->postJson(route('video.finished', $this->lesson))->assertOk()->assertJson(['status' => 'processing', 'progress' => 40]);
        $this->get(route('video.status', $this->lesson))->assertJson(['status' => 'processing']);

        // Bunny finishes: the scheduled sync swaps the video in and deletes the old one.
        $this->bunnyStatus = ['status' => 4, 'length' => 312, 'encodeProgress' => 100];
        $this->artisan('zirak:video-sync')->expectsOutput('Checked 1 videos.');
        $this->lesson->refresh();
        $this->assertSame('guid-1', $this->lesson->video_id);
        $this->assertNull($this->lesson->video_pending_id);
        $this->assertNull($this->lesson->video_status);
        $this->assertSame(312, $this->lesson->duration_seconds);
        Http::assertSent(fn ($r) => $r->method() === 'DELETE' && str_ends_with($r->url(), '/videos/old-video'));
        $this->get(route('video.status', $this->lesson))->assertJson(['status' => 'ready']);
    }

    public function test_signed_webhook_updates_the_lesson_after_asking_bunny(): void
    {
        $this->actingAs($this->teacher)->postJson(route('video.start', $this->lesson));
        $this->bunnyStatus = ['status' => 4, 'length' => 60, 'encodeProgress' => 100];

        // Wrong or missing signature: refused, nothing changes.
        [$body, $headers] = $this->signed(['VideoLibraryId' => 4242, 'VideoGuid' => 'guid-1', 'Status' => 3]);
        $this->call('POST', '/webhooks/bunny', [], [], [], [...$headers, 'HTTP_X-BunnyStream-Signature' => str_repeat('0', 64)], $body)->assertUnauthorized();
        $this->call('POST', '/webhooks/bunny', [], [], [], ['CONTENT_TYPE' => 'application/json'], $body)->assertUnauthorized();
        $this->assertSame('guid-1', $this->lesson->fresh()->video_pending_id);

        $server = collect($headers)->mapWithKeys(fn ($v, $k) => [$k === 'CONTENT_TYPE' ? $k : 'HTTP_'.strtoupper(str_replace('-', '_', $k)) => $v])->all();
        $this->call('POST', '/webhooks/bunny', [], [], [], $server, $body)->assertOk();
        $this->assertSame('guid-1', $this->lesson->fresh()->video_id);
    }

    public function test_failed_processing_keeps_the_old_video_and_a_new_upload_starts_fresh(): void
    {
        $this->actingAs($this->teacher)->postJson(route('video.start', $this->lesson));
        $this->postJson(route('video.finished', $this->lesson));
        $this->bunnyStatus = ['status' => 5, 'length' => 0, 'encodeProgress' => 0];
        $this->artisan('zirak:video-sync');

        $this->lesson->refresh();
        $this->assertSame('failed', $this->lesson->video_status);
        $this->assertSame('old-video', $this->lesson->video_id);

        $this->postJson(route('video.start', $this->lesson))->assertJsonPath('headers.VideoId', 'guid-2');
        Http::assertSent(fn ($r) => $r->method() === 'DELETE' && str_ends_with($r->url(), '/videos/guid-1'));
    }

    public function test_students_only_see_a_ready_video(): void
    {
        $student = User::factory()->create(['locale' => 'en']);
        app(EnrollmentService::class)->enroll($student, $this->lesson->chapter->course, 'free');
        $this->lesson->update(['video_id' => null]);
        $this->actingAs($this->teacher)->postJson(route('video.start', $this->lesson));

        $url = route('chapters.show', [$this->lesson->chapter->course, $this->lesson->chapter]);
        $this->actingAs($student)->get($url)->assertOk()->assertDontSee('guid-1')->assertDontSee('iframe.mediadelivery.net', false);
    }

    public function test_upload_window_and_settings(): void
    {
        $this->actingAs($this->teacher)->get('/teach')->assertSee('video-upload', false);

        config(['services.bunny.api_key' => null]);
        $this->postJson(route('video.start', $this->lesson))->assertStatus(503);
        $this->assertSame('old-video', $this->lesson->fresh()->video_id);
    }
}
