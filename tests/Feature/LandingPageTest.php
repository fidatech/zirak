<?php

namespace Tests\Feature;

use App\Filament\Resources\AiLevels\Pages\EditAiLevel;
use App\Models\AiLevel;
use App\Models\Course;
use App\Models\User;
use Database\Seeders\DemoContentSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_in_three_languages_leads_with_the_ai_levels(): void
    {
        $this->seed(DemoContentSeeder::class);

        foreach (['prs' => 'rtl', 'ps' => 'rtl', 'en' => 'ltr'] as $locale => $dir) {
            app()->setLocale($locale);
            $first = AiLevel::active()->first();
            $this->get('/?lang='.$locale)->assertOk()
                ->assertSee('dir="'.$dir.'"', false)
                ->assertSee(__('app.home2.badge', [], $locale))
                ->assertSee(__('app.levels.find_title', [], $locale))
                ->assertSee('id="levels"', false)->assertSee('id="level-1"', false)
                ->assertSee($first->text('title'))
                ->assertSee(__('app.home2.market_title', [], $locale))
                ->assertSee(route('register', ['as' => 'teacher']))->assertSee(route('levels'))->assertSee(route('certificates.verify'));
        }

        // The marketplace shows real published courses; a course without a teacher says "By Zirak".
        $course = Course::published()->whereNull('teacher_id')->whereNull('instructor_name')->first();
        $page = $this->get('/?lang=en')->assertSee(Course::published()->first()->title);
        if ($course) {
            $page->assertSee(__('app.courseui.by', ['name' => __('app.brand', [], 'en')], 'en'));
        }
    }

    public function test_levels_page_recommends_a_level_and_links_a_level_to_its_course(): void
    {
        $this->seed(DemoContentSeeder::class);
        $this->get('/levels?lang=en')->assertOk()->assertSee(__('app.levels.title', ['count' => n(5)], 'en'))->assertDontSee(__('app.levels.recommended', [], 'en'));
        $this->get('/levels?from=3&lang=en')->assertOk()->assertSee(__('app.levels.recommended', [], 'en'))->assertSee(__('app.levels.start_level', ['n' => 3], 'en'));
        $this->get('/levels?from=99&lang=en')->assertOk()->assertDontSee(__('app.levels.recommended', [], 'en'));

        // Linked to a published course: the level shows that course's price and leads to it.
        $course = Course::published()->where('is_free', false)->firstOrFail();
        AiLevel::where('number', 2)->update(['course_id' => $course->id]);
        $this->get('/levels?lang=en')->assertSee(route('courses.show', $course))->assertSee(afn($course->price_afn));

        // A hidden level disappears everywhere.
        AiLevel::where('number', 5)->update(['is_active' => false]);
        app()->setLocale('en');
        $this->get('/?lang=en')->assertDontSee('id="level-5"', false);
    }

    public function test_admin_edits_ai_levels_in_three_languages(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/admin/ai-levels')->assertOk()->assertSee('AI Basics');
        $level = AiLevel::where('number', 1)->firstOrFail();
        $this->get('/admin/ai-levels/'.$level->id.'/edit')->assertOk();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::test(EditAiLevel::class, ['record' => $level->getRouteKey()])
            ->fillForm(['content.en.title' => 'AI for everyone', 'content.prs.title' => 'هوش مصنوعی برای همه', 'content.en.duration' => '3 weeks'])
            ->call('save')->assertHasNoFormErrors();
        $level->refresh();
        $this->assertSame('AI for everyone', $level->content['en']['title']);
        $this->assertSame('هوش مصنوعی برای همه', $level->content['prs']['title']);
        $this->assertSame('3 weeks', $level->content['en']['duration']);
        $this->assertNotEmpty($level->content['ps']['title']); // other languages kept
        auth()->logout();
        $this->get('/?lang=en')->assertSee('AI for everyone')->assertSee('3 weeks');
    }

    public function test_home_page_loads_nothing_from_other_websites(): void
    {
        $html = $this->get('/?lang=prs')->getContent();
        preg_match_all('/(?:src|href)="(https?:\/\/[^"]+)"/', $html, $m);
        $external = array_filter($m[1], fn ($url) => ! str_starts_with($url, config('app.url')) && ! str_starts_with($url, 'http://localhost'));
        $this->assertSame([], array_values($external));
        $this->assertStringNotContainsString('<img', $html); // drawn with CSS, light on mobile data
    }

    public function test_logged_in_students_still_get_their_dashboard(): void
    {
        $this->actingAs(User::factory()->create())->get('/')->assertOk()
            ->assertDontSee('id="levels"', false);
    }
}
