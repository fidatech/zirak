<?php

namespace App\Http\Controllers;

use App\Enums\CourseLevel;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request, ?Category $category = null): View
    {
        abort_if($category && ! $category->is_active, 404);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'level' => ['nullable', 'array'],
            'level.*' => ['string'],
            'language' => ['nullable', 'array'],
            'language.*' => ['string'],
            'price' => ['nullable', 'in:free,paid'],
        ]);

        $courses = Course::published()->with('category')->withCount('lessons')
            ->when($category, fn ($q) => $q->where('category_id', $category->id))
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($w) => $w->where('title', 'like', "%{$term}%")->orWhere('subtitle', 'like', "%{$term}%")))
            ->when($filters['level'] ?? null, fn ($q, $levels) => $q->whereIn('level', $levels))
            ->when($filters['language'] ?? null, fn ($q, $langs) => $q->whereIn('language', $langs))
            ->when(($filters['price'] ?? null) === 'free', fn ($q) => $q->where('is_free', true))
            ->when(($filters['price'] ?? null) === 'paid', fn ($q) => $q->where('is_free', false))
            ->orderByDesc('is_featured')->latest('published_at')
            ->paginate(12)->withQueryString();

        return view('catalog.index', [
            'category' => $category,
            'categories' => Category::active()->withCount(['courses' => fn ($q) => $q->published()])->get(),
            'courses' => $courses,
            'levels' => CourseLevel::options(),
            'filters' => $filters,
        ]);
    }
}
