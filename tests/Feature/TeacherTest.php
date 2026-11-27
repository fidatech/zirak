<?php

namespace Tests\Feature;

use App\Enums\CourseStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Courses\Pages\ListCourses as AdminListCourses;
use App\Filament\Resources\TeacherApplications\Pages\ListTeacherApplications;
use App\Filament\Resources\TeacherPayouts\Pages\ListTeacherPayouts;
use App\Filament\Teach\Resources\Chapters\Pages\EditChapter;
use App\Filament\Teach\Resources\Chapters\RelationManagers\LessonsRelationManager;
use App\Filament\Teach\Resources\Courses\Pages\CreateCourse;
use App\Filament\Teach\Resources\Courses\Pages\EditCourse;
use App\Filament\Teach\Widgets\TeacherStats;
use App\Filament\Widgets\TeacherBalances;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Order;
use App\Models\TeacherApplication;
use App\Models\TeacherPayout;
use App\Models\TutorMessage;
use App\Models\User;
use App\Services\EnrollmentService;
use App\Services\TeacherEarnings;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class TeacherTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $teacher;

    private User $otherTeacher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->teacher = User::factory()->create(['role' => UserRole::Instructor, 'name' => 'Maryam Ahmadi', 'nickname' => 'Ustad Maryam', 'phone' => '+93791112233', 'locale' => 'en']);
        $this->otherTeacher = User::factory()->create(['role' => UserRole::Instructor, 'locale' => 'en']);
    }

    private function course(User $teacher, array $attrs = []): Course
    {
        $category = Category::first() ?? Category::create(['slug' => 'design', 'name_prs' => 'دیزاین', 'name_ps' => 'ډیزاین', 'name_en' => 'Design', 'color' => 'teal', 'icon' => 'pencil']);

        return Course::create([
            'category_id' => $category->id, 'teacher_id' => $teacher->id, 'slug' => 'course-'.uniqid(), 'title' => 'Logo design',
            'description' => 'Learn logo design.', 'status' => CourseStatus::Draft, 'price_afn' => 1000, 'language' => 'prs', ...$attrs,
        ]);
    }

    private function completeChapter(Course $course): Chapter
    {
        $chapter = $course->chapters()->create(['title' => 'Basics', 'sort' => 1]);
        $chapter->lessons()->create(['title' => 'What is a logo?', 'body' => 'Text', 'sort' => 1]);
        $chapter->questions()->create(['kind' => 'test', 'body' => 'Q?', 'options' => ['A', 'B'], 'correct_index' => 0, 'sort' => 1]);

        return $chapter;
    }

    private function adminPanel(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    private function teachPanel(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('teach'));
    }

    public function test_student_applies_and_admin_approves(): void
    {
        config(['services.telegram.bot_token' => '1:X', 'services.telegram.bot_username' => 'ZirakAfBot']);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => []])]);
        $student = User::factory()->create(['telegram_id' => 777, 'locale' => 'en']);

        $this->actingAs($student)->get('/profile')->assertSee(__('app.teach.cta'));
        $this->get('/become-a-teacher')->assertOk()->assertSee(__('app.teach.send'));
        $form = ['full_name' => 'Ahmad Zia', 'headline' => 'Video editor', 'about' => str_repeat('I edit videos for TV. ', 3),
            'course_idea' => 'Video editing on a phone for beginners.', 'languages' => ['ps', 'prs'], 'sample_url' => 'https://youtube.com/@ahmad'];
        $this->post('/become-a-teacher', $form)->assertRedirect('/become-a-teacher');
        $this->post('/become-a-teacher', $form); // a second press doesn't create a second application
        $this->assertSame(1, TeacherApplication::count());
        $this->get('/become-a-teacher')->assertSee(__('app.teach.pending_title'));
        $this->get('/teach')->assertForbidden();

        $this->actingAs($this->admin);
        $this->adminPanel();
        Livewire::test(ListTeacherApplications::class)
            ->callTableAction('approve', TeacherApplication::first())
            ->assertNotified('Teacher approved');

        $student->refresh();
        $this->assertSame(UserRole::Instructor, $student->role);
        $this->assertSame('Video editor', $student->teacher_headline);
        Http::assertSent(fn ($r) => str_contains($r->url(), '/sendMessage') && $r['chat_id'] === 777 && str_contains($r['text'], 'Zirak teacher'));

        $this->actingAs($student)->get('/teach')->assertOk();
        $this->get('/become-a-teacher')->assertRedirect('/teach');
    }

    public function test_rejected_applicant_sees_the_note_and_can_apply_again(): void
    {
        $student = User::factory()->create();
        $application = $student->teacherApplications()->create(['full_name' => 'A', 'headline' => 'B', 'about' => 'C', 'course_idea' => 'D', 'languages' => ['prs']]);

        $this->actingAs($this->admin);
        $this->adminPanel();
        Livewire::test(ListTeacherApplications::class)->callTableAction('reject', $application, ['note' => 'Please add a sample video.']);

        $this->actingAs($student)->get('/become-a-teacher')->assertSee('Please add a sample video.')->assertSee(__('app.teach.send'));
        $this->assertSame(UserRole::Student, $student->fresh()->role);
    }

    public function test_guests_go_to_login_and_students_cannot_enter_the_teacher_area(): void
    {
        $this->get('/teach')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/teach')->assertForbidden();
        $this->actingAs($this->teacher)->get('/teach')->assertOk();
        $this->actingAs($this->teacher)->get('/admin')->assertForbidden();
    }

    public function test_a_teacher_only_sees_and_edits_their_own_courses(): void
    {
        $mine = $this->course($this->teacher, ['title' => 'My logo course']);
        $theirs = $this->course($this->otherTeacher, ['title' => 'Someone else course']);
        $theirChapter = $this->completeChapter($theirs);

        $this->actingAs($this->teacher);
        $this->get('/teach/courses')->assertOk()->assertSee('My logo course')->assertDontSee('Someone else course');
        $this->get('/teach/courses/'.$mine->getRouteKey().'/edit')->assertOk();
        $this->get('/teach/courses/'.$theirs->getRouteKey().'/edit')->assertNotFound();
        $this->get('/teach/chapters/'.$theirChapter->id.'/edit')->assertNotFound();
    }

    public function test_teacher_creates_a_draft_and_cannot_publish_it_themselves(): void
    {
        $this->actingAs($this->teacher);
        $this->teachPanel();

        Livewire::test(CreateCourse::class)
            ->fillForm([
                'title' => 'Phone photography', 'slug' => 'phone-photography', 'category_id' => Category::create(['slug' => 'photo', 'name_prs' => 'عکاسی', 'name_ps' => 'عکاسي', 'name_en' => 'Photo', 'color' => 'teal', 'icon' => 'pencil'])->id,
                'language' => 'prs', 'level' => 'beginner', 'price_afn' => 500,
                'status' => 'published', 'teacher_share' => 100, 'teacher_id' => $this->otherTeacher->id, // not on the teacher form: ignored
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $course = Course::where('slug', 'phone-photography')->firstOrFail();
        $this->assertSame($this->teacher->id, $course->teacher_id);
        $this->assertSame(CourseStatus::Draft, $course->status);
        $this->assertNull($course->teacher_share);
        $this->assertSame('Ustad Maryam', $course->instructor_name);

        // Not shown to students, but the teacher can preview it.
        $this->get(route('courses.show', $course))->assertOk();
        $this->actingAs(User::factory()->create())->get(route('courses.show', $course))->assertNotFound();
    }

    public function test_review_flow_send_request_changes_approve(): void
    {
        $course = $this->course($this->teacher);
        $this->actingAs($this->teacher);
        $this->teachPanel();

        // Not complete yet: stays a draft.
        Livewire::test(EditCourse::class, ['record' => $course->getRouteKey()])->callAction('submit')->assertNotified('Not ready yet');
        $this->assertSame(CourseStatus::Draft, $course->fresh()->status);

        $this->completeChapter($course);
        Livewire::test(EditCourse::class, ['record' => $course->getRouteKey()])->callAction('submit')->assertNotified('Sent for review');
        $this->assertSame(CourseStatus::Review, $course->fresh()->status);
        $this->assertNotNull($course->fresh()->submitted_at);

        $this->actingAs($this->admin);
        $this->adminPanel();
        Livewire::test(AdminListCourses::class)->callTableAction('changes', $course, ['note' => 'Add a second chapter.']);
        $this->assertSame(CourseStatus::Draft, $course->fresh()->status);

        $this->actingAs($this->teacher);
        $this->get('/teach/courses/'.$course->getRouteKey().'/edit')->assertSee('Add a second chapter.');
        $this->teachPanel();
        Livewire::test(EditCourse::class, ['record' => $course->getRouteKey()])->callAction('submit');

        $this->actingAs($this->admin);
        $this->adminPanel();
        Livewire::test(AdminListCourses::class)->callTableAction('approve', $course);
        $course->refresh();
        $this->assertSame(CourseStatus::Published, $course->status);
        $this->assertNotNull($course->published_at);

        // Course page links to the teacher's public page.
        $this->get(route('courses.show', $course))->assertSee('Ustad Maryam')->assertSee(route('teachers.show', $this->teacher));
    }

    public function test_teacher_cannot_set_the_lesson_video_or_change_price_after_publishing(): void
    {
        $course = $this->course($this->teacher, ['status' => CourseStatus::Published]);
        $chapter = $this->completeChapter($course);
        $this->actingAs($this->teacher);
        $this->teachPanel();

        Livewire::test(EditCourse::class, ['record' => $course->getRouteKey()])
            ->assertFormFieldIsDisabled('price_afn')
            ->fillForm(['price_afn' => 1])->call('save');
        $this->assertSame(1000, $course->fresh()->price_afn);

        Livewire::test(LessonsRelationManager::class, ['ownerRecord' => $chapter, 'pageClass' => EditChapter::class])
            ->callTableAction('edit', $chapter->lessons->first(), ['title' => 'Edited', 'video_id' => 'evil-video']);
        $this->assertSame('Edited', $chapter->lessons->first()->fresh()->title);
        $this->assertNull($chapter->lessons->first()->fresh()->video_id);
    }

    public function test_earnings_are_fixed_at_payment_and_payouts_lower_the_balance(): void
    {
        $course = $this->course($this->teacher, ['status' => CourseStatus::Published, 'teacher_share' => 60]);
        $other = $this->course($this->otherTeacher, ['status' => CourseStatus::Published]); // default 70%
        $buyer = User::factory()->create(['nickname' => 'Shabnam', 'phone' => '+93700000001']);
        $pay = function (Course $c, int $total, string $currency = 'AFN') use ($buyer) {
            $order = Order::create(['user_id' => $buyer->id, 'course_id' => $c->id, 'status' => 'pending', 'gateway' => 'hesabpay', 'currency' => $currency, 'subtotal' => $total, 'total' => $total]);

            return app(EnrollmentService::class)->markPaid($order, 'tx-'.uniqid());
        };

        $first = $pay($course, 1000);
        $this->assertSame(60, $first->teacher_share);
        $this->assertSame(600, $first->teacher_amount);
        $this->assertSame(490, $pay($other, 700)->teacher_amount);
        $pay($course, 999, 'USD'); // $9.99 → 599 cents

        // Changing the share later does not change past sales.
        $course->update(['teacher_share' => 10]);
        $refunded = $pay($course, 1000);
        $refunded->update(['status' => 'refunded']);

        $earnings = app(TeacherEarnings::class);
        $this->assertSame(['earned' => 600, 'paid_out' => 0, 'balance' => 600], $earnings->summary($this->teacher)['AFN']);
        $this->assertSame(599, $earnings->balance($this->teacher, 'USD'));

        $this->actingAs($this->admin);
        $this->adminPanel();
        Livewire::test(TeacherBalances::class)->assertSee('600 AFN')->assertSee('$5.99');
        Livewire::test(ListTeacherPayouts::class)
            ->callAction('create', ['teacher_id' => $this->teacher->id, 'currency' => 'AFN', 'amount' => 400, 'method' => 'hesabpay', 'reference' => 'HP-1', 'paid_on' => now()->toDateString()])
            ->assertHasNoActionErrors();
        $this->assertSame(200, $earnings->balance($this->teacher, 'AFN'));
        $this->assertSame($this->admin->id, TeacherPayout::first()->created_by);

        // The teacher sees their own sales and payouts, students by nickname only.
        $this->actingAs($this->teacher);
        $this->get('/teach/sales')->assertOk()->assertSee('Shabnam')->assertDontSee('700000001')->assertSee('600 AFN')->assertDontSee('490 AFN');
        $this->get('/teach/payouts')->assertOk()->assertSee('400 AFN')->assertSee('HP-1');
        $this->get('/teach')->assertOk()->assertSeeLivewire(TeacherStats::class);
        $this->teachPanel();
        Livewire::test(TeacherStats::class)->assertSee('200 AFN')->assertSee('$5.99');
    }

    public function test_teacher_answers_questions_from_their_own_courses_only(): void
    {
        $mine = $this->course($this->teacher, ['status' => CourseStatus::Published]);
        $theirs = $this->course($this->otherTeacher, ['status' => CourseStatus::Published]);
        $student = User::factory()->create();
        foreach ([[$mine, 'How do I pick colours?'], [$theirs, 'Question for someone else']] as [$c, $text]) {
            TutorMessage::create(['user_id' => $student->id, 'course_id' => $c->id, 'role' => 'student', 'content' => $text, 'locale' => 'prs', 'escalated_at' => now()]);
        }

        $this->actingAs($this->teacher)->get('/teach/student-questions')->assertOk()
            ->assertSee('How do I pick colours?')->assertDontSee('Question for someone else');
    }

    public function test_teacher_area_speaks_the_teachers_language(): void
    {
        $course = $this->course($this->teacher, ['status' => CourseStatus::Published, 'teacher_share' => 60]);
        $buyer = User::factory()->create();
        $order = Order::create(['user_id' => $buyer->id, 'course_id' => $course->id, 'status' => 'pending', 'gateway' => 'hesabpay', 'currency' => 'AFN', 'subtotal' => 1000, 'total' => 1000]);
        app(EnrollmentService::class)->markPaid($order, 'tx-lang');

        // Dari: right-to-left, our own labels and Filament's (via Persian).
        $this->teacher->update(['locale' => 'prs']);
        $this->actingAs($this->teacher)->get('/teach/courses')->assertOk()
            ->assertSee('dir="rtl"', false)->assertSee('دوره‌های من')->assertSee('دوره جدید')
            ->assertSee('Vazirmatn', false)->assertDontSee('fonts.bunny.net', false)->assertDontSee('ui-avatars.com', false);
        $this->get('/teach/sales')->assertSee('۶۰۰ افغانی')->assertSee('۶۰٪');

        // Pashto, chosen from the language menu and saved on the profile.
        // (The side menu is built once per process, so check page content, which is built on every request.)
        $this->get('/teach/courses?lang=ps')->assertOk()->assertSee('dir="rtl"', false)->assertSee('نوی کورس')->assertSee('لټون');
        $this->assertSame('ps', $this->teacher->fresh()->locale);
        $this->get('/teach/courses/'.$course->getRouteKey().'/edit')->assertSee('بدلونونه ساتل');

        // English.
        $this->get('/teach/courses?lang=en')->assertSee('dir="ltr"', false)->assertSee('New course');

        // Admin stays in English whatever the admin's own language is.
        $this->admin->update(['locale' => 'ps']);
        $this->actingAs($this->admin)->get('/admin/courses')->assertOk()->assertSee('dir="ltr"', false)->assertDontSee('زما کورسونه');
    }

    public function test_teacher_language_files_have_the_same_keys(): void
    {
        $keys = fn (string $locale) => array_keys(Arr::dot(require lang_path("{$locale}/teach.php")));
        $this->assertSame($keys('en'), $keys('prs'));
        $this->assertSame($keys('en'), $keys('ps'));
    }

    public function test_public_teacher_page_never_shows_the_phone(): void
    {
        $this->course($this->teacher, ['status' => CourseStatus::Published, 'title' => 'Published logo course']);
        $this->course($this->teacher, ['title' => 'Secret draft']);
        $this->teacher->update(['teacher_headline' => 'Designer', 'teacher_bio' => 'Ten years of design.']);

        $this->get(route('teachers.show', $this->teacher))->assertOk()
            ->assertSee('Ustad Maryam')->assertSee('Ten years of design.')->assertSee('Published logo course')
            ->assertDontSee('Secret draft')->assertDontSee('791112233')->assertDontSee('Maryam Ahmadi');

        $this->get(route('teachers.show', User::factory()->create()))->assertNotFound();
    }
}
