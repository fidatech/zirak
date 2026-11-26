<?php

namespace Tests\Feature;

use App\Filament\Resources\StudentQuestions\Pages\ListStudentQuestions;
use App\Models\Course;
use App\Models\TutorMessage;
use App\Models\User;
use App\Services\EnrollmentService;
use App\Services\Tutor\CourseContext;
use App\Services\Tutor\TutorService;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class TutorTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoContentSeeder::class);
        $this->user = User::factory()->create();
        $this->course = Course::where('slug', 'ai-beginner')->firstOrFail();
        app(EnrollmentService::class)->enroll($this->user, $this->course, 'free');
        config(['zirak.tutor.driver' => 'fake', 'zirak.tutor.daily_limit' => 20]);
    }

    private function lesson()
    {
        return $this->course->chapters->last()->lessons->last(); // "نوشتن یک پرامپت خوب"
    }

    public function test_enrolled_student_gets_an_answer_and_history(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('tutor.ask', $this->course), ['question' => 'پرامپت خوب چیست؟', 'lesson_id' => $this->lesson()->id])
            ->assertOk()
            ->assertJsonPath('messages.0.role', 'student')
            ->assertJsonPath('messages.1.role', 'ai')
            ->assertJsonPath('remaining', 19);

        $this->getJson(route('tutor.history', $this->course))
            ->assertOk()->assertJsonPath('available', true)->assertJsonCount(2, 'messages');

        $this->assertDatabaseHas('tutor_messages', ['role' => 'ai', 'lesson_id' => $this->lesson()->id]);
    }

    public function test_claude_api_gets_course_material_language_and_history(): void
    {
        config(['zirak.tutor.driver' => 'anthropic', 'zirak.tutor.api_key' => 'sk-test', 'zirak.tutor.model' => 'claude-haiku-5-5']);
        Http::fake(['api.anthropic.com/v1/messages' => Http::sequence()
            ->push(['content' => [['type' => 'text', 'text' => 'جواب اول']], 'usage' => ['input_tokens' => 900, 'output_tokens' => 50]])
            ->push(['content' => [['type' => 'text', 'text' => 'ځواب دوهم']], 'usage' => ['input_tokens' => 950, 'output_tokens' => 40]])]);

        $this->actingAs($this->user);
        $this->postJson(route('tutor.ask', $this->course), ['question' => 'پرامپت خوب چند بخش دارد؟', 'lesson_id' => $this->lesson()->id])
            ->assertOk()->assertJsonPath('messages.1.content', 'جواب اول');

        Http::assertSent(fn ($request) => $request->hasHeader('x-api-key', 'sk-test')
            && $request->hasHeader('anthropic-version', '2023-06-01')
            && $request['model'] === 'claude-haiku-5-5'
            && str_contains($request['system'], 'Dari')
            && str_contains($request['system'], 'CURRENT LESSON')
            && str_contains($request['system'], 'یک پرامپت خوب پنج بخش دارد')
            && $request['messages'] === [['role' => 'user', 'content' => 'پرامپت خوب چند بخش دارد؟']]);

        // Second question in Pashto includes the previous exchange.
        $this->postJson(route('tutor.ask', $this->course).'?lang=ps', ['question' => 'بېلګه راکړه'])->assertOk();
        Http::assertSent(fn ($request) => count($request['messages']) === 3
            && str_contains($request['system'], 'Pashto')
            && $request['messages'][1] === ['role' => 'assistant', 'content' => 'جواب اول']);

        $this->assertSame(900, TutorMessage::where('role', 'ai')->first()->tokens_in);
    }

    public function test_api_failure_shows_a_friendly_error_and_saves_nothing(): void
    {
        config(['zirak.tutor.driver' => 'anthropic', 'zirak.tutor.api_key' => 'sk-test']);
        Http::fake(['*' => Http::response(['error' => 'overloaded'], 529)]);

        $this->actingAs($this->user)->postJson(route('tutor.ask', $this->course), ['question' => 'سلام'])
            ->assertStatus(422)->assertJsonPath('errors.question.0', __('app.tutor.error'));
        $this->assertDatabaseCount('tutor_messages', 0);
    }

    public function test_daily_limit(): void
    {
        config(['zirak.tutor.daily_limit' => 2]);
        $this->actingAs($this->user);
        $this->postJson(route('tutor.ask', $this->course), ['question' => 'یک'])->assertOk()->assertJsonPath('remaining', 1);
        $this->postJson(route('tutor.ask', $this->course), ['question' => 'دو'])->assertOk()->assertJsonPath('remaining', 0);
        $this->postJson(route('tutor.ask', $this->course), ['question' => 'سه'])->assertStatus(422);
        $this->assertSame(2, TutorMessage::where('role', 'student')->count());
    }

    public function test_only_enrolled_students_can_use_the_tutor(): void
    {
        $other = Course::where('slug', 'mobile-filmmaking')->firstOrFail();
        $this->actingAs($this->user);
        $this->getJson(route('tutor.history', $other))->assertForbidden();
        $this->postJson(route('tutor.ask', $other), ['question' => 'سلام'])->assertForbidden();
        $this->actingAs(User::factory()->create())->postJson(route('tutor.ask', $this->course), ['question' => 'سلام'])->assertForbidden();
    }

    public function test_lesson_from_another_course_is_ignored(): void
    {
        $foreign = Course::where('slug', 'mobile-filmmaking')->firstOrFail()->chapters->first()->lessons->first();
        $this->actingAs($this->user)->postJson(route('tutor.ask', $this->course), ['question' => 'سلام', 'lesson_id' => $foreign->id])->assertOk();
        $this->assertNull(TutorMessage::first()->lesson_id);
    }

    public function test_fake_tutor_is_off_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        $this->assertFalse(app(TutorService::class)->available());
        config(['zirak.tutor.driver' => 'anthropic', 'zirak.tutor.api_key' => null]);
        $this->assertFalse(app(TutorService::class)->available());
        config(['zirak.tutor.api_key' => 'sk-live']);
        $this->assertTrue(app(TutorService::class)->available());
    }

    public function test_escalate_to_teacher_and_teacher_answer_appears(): void
    {
        $this->actingAs($this->user);
        $answerId = $this->postJson(route('tutor.ask', $this->course), ['question' => 'سوال سخت'])->json('messages.1.id');

        $this->actingAs(User::factory()->create())->postJson(route('tutor.escalate', $answerId))->assertForbidden();
        $this->actingAs($this->user)->postJson(route('tutor.escalate', $answerId))->assertOk();
        $this->actingAs($this->user)->postJson(route('tutor.rate', $answerId), ['helpful' => false])->assertOk();

        $question = TutorMessage::where('role', 'student')->firstOrFail();
        $this->assertNotNull($question->escalated_at);
        $this->assertSame(1, TutorMessage::waitingForTeacher()->count());

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/admin/student-questions')->assertOk()->assertSee('سوال سخت');
        Livewire::test(ListStudentQuestions::class)
            ->callTableAction('answer', $question, data: ['answer' => 'جواب استاد'])
            ->assertHasNoTableActionErrors();

        $this->assertSame(0, TutorMessage::waitingForTeacher()->count());
        $this->actingAs($this->user)->getJson(route('tutor.history', $this->course))
            ->assertJsonPath('messages.2.role', 'teacher')->assertJsonPath('messages.2.content', 'جواب استاد')
            ->assertJsonPath('messages.1.helpful', false);
    }

    public function test_pages_show_the_tutor_panel(): void
    {
        $chapter = $this->course->chapters->first();
        $this->actingAs($this->user)->get(route('chapters.show', [$this->course, $chapter, 'learn']))->assertOk()->assertSee(__('app.tutor.title'));
        $this->get(route('courses.show', $this->course))->assertOk()->assertSee(__('app.tutor.title'));
    }

    public function test_context_ranks_matching_lessons_and_normalizes_letters(): void
    {
        $context = app(CourseContext::class);
        $this->assertContains('یادگیری', $context->words('يادگيري!'));
        $text = $context->build($this->course, null, 'خلاصه کردن متن با هوش مصنوعی', 9000);
        $this->assertStringContainsString('AI چه کارهایی می‌تواند', $text);
    }
}
