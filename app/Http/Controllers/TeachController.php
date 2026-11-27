<?php

namespace App\Http\Controllers;

use App\Enums\CourseStatus;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\TeacherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeachController extends Controller
{
    /** "Teach on Zirak": apply, or see the application's status. */
    public function apply(Request $request, TeacherService $teachers): View|RedirectResponse
    {
        $user = $request->user();
        if ($user->isTeacher()) {
            return redirect('/teach');
        }

        return view('teach.apply', [
            'pending' => $teachers->pendingApplication($user),
            'last' => $user->teacherApplications()->latest('id')->first(),
        ]);
    }

    public function submit(Request $request, TeacherService $teachers): RedirectResponse
    {
        $user = $request->user();
        abort_if($user->isTeacher(), 403);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'headline' => ['required', 'string', 'max:120'],
            'about' => ['required', 'string', 'min:30', 'max:2000'],
            'course_idea' => ['required', 'string', 'min:20', 'max:2000'],
            'languages' => ['required', 'array', 'min:1'],
            'languages.*' => ['in:prs,ps,en'],
            'sample_url' => ['nullable', 'url', 'max:500'],
        ]);
        $teachers->apply($user, $data);

        return redirect()->route('teach.apply')->with('status', __('app.teach.sent'));
    }

    /** Public teacher page. Shows the public name only, never contact details. */
    public function show(User $teacher): View
    {
        $courses = $teacher->taughtCourses()->where('status', CourseStatus::Published)->forCards()->latest('published_at')->get();
        abort_unless($teacher->isTeacher() || $courses->isNotEmpty(), 404);

        return view('teach.show', [
            'teacher' => $teacher,
            'courses' => $courses,
            'students' => Enrollment::whereIn('course_id', $courses->pluck('id'))->distinct('user_id')->count('user_id'),
        ]);
    }
}
