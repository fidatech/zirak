<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request, ProgressService $progress): View
    {
        $categories = Category::active()->withCount(['courses' => fn ($q) => $q->published()])->get();
        $user = $request->user();

        if (! $user) {
            $featured = Course::published()->with('category')->orderByDesc('is_featured')->latest('published_at')->take(6)->get();

            return view('home.guest', compact('categories', 'featured'));
        }

        $myCourses = $user->courses()->with('category')->latest('enrollments.updated_at')->get()
            ->map(fn (Course $c) => ['course' => $c, 'state' => $progress->courseState($user, $c)]);

        $next = $myCourses->first(fn ($row) => $row['state']['next'] !== null);
        $featured = Course::published()->with('category')->whereNotIn('id', $myCourses->pluck('course.id'))
            ->orderByDesc('is_featured')->take(4)->get();

        return view('home.dashboard', [
            'categories' => $categories,
            'myCourses' => $myCourses,
            'next' => $next,
            'weak' => $progress->weakTopics($user, 4),
            'streak' => $progress->streak($user),
            'featured' => $featured,
        ]);
    }
}
