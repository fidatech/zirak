<?php

namespace Tests\Feature;

use App\Enums\CourseStatus;
use App\Enums\UserRole;
use App\Filament\Teach\Resources\PlanEarnings\Pages\ListPlanEarnings;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\TeacherPoolEarning;
use App\Models\User;
use App\Services\EnrollmentService;
use App\Services\TeacherEarnings;
use App\Services\TeacherPool;
use Database\Seeders\DemoContentSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoContentSeeder::class);
        config(['services.hesabpay.driver' => 'fake', 'zirak.subscription.price_afn' => 999, 'zirak.subscription.days' => 30]);
        $this->user = User::factory()->create(['nickname' => 'Shabnam']);
        $this->course = Course::where('slug', 'mobile-filmmaking')->firstOrFail(); // paid course
    }

    private function subscribe(User $user): Order
    {
        $this->actingAs($user)->post(route('plans.subscribe'))->assertRedirect();
        $order = Order::where('user_id', $user->id)->latest('id')->firstOrFail();
        $this->post(route('payments.fake.pay', $order->number))->assertRedirect();

        return $order->fresh();
    }

    public function test_plans_page_in_three_languages_and_course_page_offers_the_plan(): void
    {
        foreach (['prs', 'ps', 'en'] as $lang) {
            $this->get('/plans?lang='.$lang)->assertOk()->assertSee(__('app.plan.hero_title', [], $lang));
        }
        $this->get('/plans?lang=en')->assertSee('999');
        $this->get(route('courses.show', $this->course).'?lang=en')->assertSee(__('app.plan.course_or', ['price' => afn(999), 'days' => n(30)], 'en'));
        config(['zirak.subscription.enabled' => false]);
        $this->get('/plans')->assertNotFound();
    }

    public function test_paying_opens_every_course_and_paying_again_adds_time(): void
    {
        $order = $this->subscribe($this->user);
        $this->assertSame('subscription', $order->kind);
        $this->assertNull($order->course_id);
        $this->assertTrue($order->isPaid());
        $this->assertTrue($this->user->hasActivePlan());
        $this->get(route('payments.return', ['order' => $order->number]))->assertOk()->assertSee(__('app.pay.success'));

        // A subscriber starts a paid course without paying again; the full lesson opens.
        $this->assertFalse($this->user->isEnrolledIn($this->course));
        $this->get(route('courses.show', $this->course))->assertSee(__('app.plan.start_with_plan'));
        $this->post(route('courses.purchase', $this->course))->assertRedirect(route('courses.show', $this->course));
        $this->assertSame('subscription', Enrollment::where('user_id', $this->user->id)->value('source'));
        $this->assertSame(1, Order::count());
        $this->assertTrue($this->user->isEnrolledIn($this->course));

        // Opening a lesson of another paid course enrolls through the plan automatically.
        $other = Course::published()->where('is_free', false)->whereKeyNot($this->course->id)->firstOrFail();
        $this->get(route('chapters.show', [$other, $other->chapters()->first()]))->assertOk()->assertDontSee(__('app.preview.banner_title'));
        $this->assertTrue($this->user->isEnrolledIn($other));

        // Paying again (and the webhook arriving twice) adds exactly one more period after the first.
        $first = Subscription::firstOrFail();
        $second = $this->subscribe($this->user);
        app(EnrollmentService::class)->markPaid($second);
        $this->assertSame(2, Subscription::count());
        $this->assertTrue(Subscription::latest('id')->first()->starts_at->equalTo($first->ends_at));
        $this->assertTrue($this->user->planEndsAt()->equalTo($first->ends_at->copy()->addDays(30)));
    }

    public function test_when_the_plan_ends_plan_courses_lock_but_bought_courses_and_progress_stay(): void
    {
        $this->subscribe($this->user);
        $this->post(route('courses.purchase', $this->course));
        $lesson = $this->course->chapters()->first()->lessons()->first();
        $this->post(route('lessons.complete', [$this->course, $lesson->chapter_id, $lesson]));
        $this->assertSame(1, LessonCompletion::count());

        $bought = Course::published()->where('is_free', false)->whereKeyNot($this->course->id)->firstOrFail();
        $this->post(route('courses.purchase', $bought)); // enrolls with the plan …
        $order = Order::create(['user_id' => $this->user->id, 'course_id' => $bought->id, 'status' => 'pending', 'gateway' => 'fake', 'currency' => 'AFN', 'subtotal' => 100, 'total' => 100]);
        app(EnrollmentService::class)->markPaid($order); // … then buys it: now it is kept forever
        $this->assertSame('order', Enrollment::where('course_id', $bought->id)->value('source'));

        $this->travel(31)->days();
        $this->assertFalse($this->user->hasActivePlan());
        $this->assertFalse($this->user->isEnrolledIn($this->course));
        $this->assertTrue($this->user->isEnrolledIn($bought));
        $this->get(route('courses.show', $this->course))->assertSee(__('app.plan.expired_note'));
        $this->get(route('chapters.show', [$this->course, $lesson->chapter_id]))->assertSee(__('app.preview.banner_title'));
        $this->post(route('lessons.complete', [$this->course, $lesson->chapter_id, $lesson]))->assertForbidden();
        $this->assertSame(1, LessonCompletion::count()); // progress kept

        // Renewing opens it again where they stopped.
        $this->subscribe($this->user);
        $this->assertTrue($this->user->isEnrolledIn($this->course));
    }

    public function test_plan_money_is_shared_by_lessons_studied_and_shows_in_teacher_balances(): void
    {
        $category = Category::firstOrFail();
        $make = function (?User $teacher, int $lessons) use ($category) {
            $course = Course::create(['category_id' => $category->id, 'teacher_id' => $teacher?->id, 'slug' => 'c-'.uniqid(), 'title' => 'Course '.uniqid(),
                'language' => 'prs', 'level' => 'beginner', 'status' => CourseStatus::Published, 'price_afn' => 500, 'is_free' => false]);
            $chapter = Chapter::create(['course_id' => $course->id, 'title' => 'Ch', 'sort' => 1]);
            foreach (range(1, $lessons) as $i) {
                Lesson::create(['chapter_id' => $chapter->id, 'title' => 'L'.$i, 'sort' => $i]);
            }

            return $course;
        };
        $maryam = User::factory()->create(['role' => UserRole::Instructor, 'locale' => 'en']);
        $ahmad = User::factory()->create(['role' => UserRole::Instructor]);
        $courseA = $make($maryam, 3);
        $courseB = $make($ahmad, 1);
        $own = $make(null, 1); // Zirak's own course

        $this->travelTo(now()->setDate(2026, 11, 10));
        $sub = User::factory()->create();
        $this->subscribe($sub);
        $other = User::factory()->create();
        Order::where('user_id', $sub->id)->update(['total' => 1000]); // 1,000 AFN of plan money in November
        foreach ([$courseA, $courseB, $own] as $course) {
            $this->actingAs($sub)->post(route('courses.purchase', $course));
            foreach ($course->chapters()->first()->lessons as $lesson) {
                $this->post(route('lessons.complete', [$course, $lesson->chapter_id, $lesson]));
            }
        }
        // A buyer's lessons never count in the pool.
        $buyer = Order::create(['user_id' => $other->id, 'course_id' => $courseB->id, 'status' => 'pending', 'gateway' => 'fake', 'currency' => 'AFN', 'subtotal' => 500, 'total' => 500]);
        app(EnrollmentService::class)->markPaid($buyer);
        LessonCompletion::create(['user_id' => $other->id, 'lesson_id' => $courseB->chapters()->first()->lessons()->first()->id, 'completed_at' => now()]);

        config(['zirak.subscription.teacher_share' => 70]);
        $this->travelTo(now()->setDate(2026, 12, 1));
        $this->artisan('zirak:plan-pool')->assertSuccessful();
        $this->artisan('zirak:plan-pool 2026-11')->assertSuccessful(); // running again changes nothing

        // pool 700 AFN; 5 lessons: Maryam 3 → 420, Ahmad 1 → 140, Zirak's course 1 → stays with Zirak.
        $this->assertSame(2, TeacherPoolEarning::count());
        $this->assertSame(420, TeacherPoolEarning::where('teacher_id', $maryam->id)->value('amount'));
        $this->assertSame(140, TeacherPoolEarning::where('teacher_id', $ahmad->id)->value('amount'));
        $this->assertSame(420, app(TeacherEarnings::class)->summary($maryam)['AFN']['earned']);
        $this->assertSame(140 + 350, app(TeacherEarnings::class)->summary($ahmad)['AFN']['earned']); // plan share + 70% of the 500 sale

        $this->actingAs($maryam);
        Filament::setCurrentPanel(Filament::getPanel('teach'));
        $this->get('/teach/plan-earnings')->assertOk();
        Livewire::test(ListPlanEarnings::class)->assertSee('420 AFN')->assertDontSee('140 AFN');
        $this->assertSame(0, app(TeacherPool::class)->compute('2026-10') === [] ? 0 : 1);
    }
}
