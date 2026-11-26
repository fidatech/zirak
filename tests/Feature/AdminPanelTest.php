<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\User;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_cannot_open_admin(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_admin_pages_render(): void
    {
        $this->seed(DemoContentSeeder::class);
        $admin = User::factory()->admin()->create();
        $course = Course::firstOrFail();
        $chapter = Chapter::firstOrFail();

        $this->actingAs($admin);
        foreach (['/admin', '/admin/categories', '/admin/categories/create', '/admin/courses', '/admin/courses/create',
            '/admin/courses/'.$course->getRouteKey().'/edit', '/admin/chapters/'.$chapter->id.'/edit', '/admin/orders',
            '/admin/coupons', '/admin/coupons/create', '/admin/certificates', '/admin/users'] as $url) {
            $this->assertSame(200, $this->get($url)->status(), $url);
        }
    }

    public function test_make_admin_command(): void
    {
        $this->artisan('zirak:admin', ['email' => 'boss@zirak.af', '--password' => 'secret-pass'])->assertSuccessful();
        $this->assertTrue(User::where('email', 'boss@zirak.af')->first()->isAdmin());
    }
}
