<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_in_three_languages_with_real_numbers(): void
    {
        $this->seed(DemoContentSeeder::class);

        foreach (['prs' => 'rtl', 'ps' => 'rtl', 'en' => 'ltr'] as $locale => $dir) {
            $this->get('/?lang='.$locale)->assertOk()
                ->assertSee('dir="'.$dir.'"', false)
                ->assertSee(__('app.landing.hero_highlight', [], $locale))
                ->assertSee(__('app.landing.how_highlight', [], $locale))
                ->assertSee('id="tutor"', false)->assertSee('id="teach"', false)
                ->assertSee(route('teach.apply'))->assertSee(route('certificates.verify'));
        }

        // The numbers band shows what is really published.
        $this->get('/?lang=en')->assertSeeInOrder([(string) Course::published()->count(), __('app.landing.stat_courses', [], 'en')]);
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
            ->assertDontSee('id="tutor"', false);
    }
}
