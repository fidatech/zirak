<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __invoke(Request $request, ProgressService $progress): View
    {
        $user = $request->user();

        return view('report.index', [
            'report' => $progress->weeklyReport($user),
            'weak' => $progress->weakTopics($user, 6),
            'courses' => $user->courses()->get()->map(fn (Course $c) => ['course' => $c, 'state' => $progress->courseState($user, $c)]),
        ]);
    }
}
