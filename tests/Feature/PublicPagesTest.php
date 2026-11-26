<?php

namespace Tests\Feature;

use App\Models\Course;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoContentSeeder::class);
    }

    public function test_guest_pages_load_in_every_language(): void
    {
        foreach (['prs' => 'rtl', 'ps' => 'rtl', 'en' => 'ltr'] as $lang => $dir) {
            foreach (['/', '/courses', '/subjects/film', '/courses/mobile-filmmaking', '/verify', '/login', '/offline'] as $url) {
                $this->get($url.'?lang='.$lang)->assertOk()->assertSee('dir="'.$dir.'"', false);
            }
        }
    }

    public function test_dari_is_the_default_language(): void
    {
        $this->get('/')->assertOk()->assertSee('زیرک')->assertSee('dir="rtl"', false);
    }

    public function test_catalog_filters(): void
    {
        $this->get('/courses?price=free')->assertOk()->assertSee('مبتدی هوش مصنوعی')->assertDontSee('فیلم‌سازی با موبایل');
        $this->get('/courses?language[]=ps')->assertOk()->assertSee('د مصنوعي ځیرکتیا پیل')->assertDontSee('مبتدی هوش مصنوعی');
        $this->get('/courses?q=فیلم')->assertOk()->assertSee('فیلم‌سازی با موبایل');
    }

    public function test_draft_course_is_hidden(): void
    {
        Course::where('slug', 'first-book')->update(['status' => 'draft']);
        $this->get('/courses/first-book')->assertNotFound();
        $this->get('/courses')->assertDontSee('نوشتن و نشر اولین کتاب');
    }

    public function test_pwa_files_exist(): void
    {
        $this->assertFileExists(public_path('manifest.webmanifest'));
        $this->assertFileExists(public_path('sw.js'));
        $this->assertFileExists(public_path('icons/icon-512.png'));
    }
}
