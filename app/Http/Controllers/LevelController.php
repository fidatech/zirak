<?php

namespace App\Http\Controllers;

use App\Models\AiLevel;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** /levels: the whole AI path. "?from=1..4" comes from the "Find your level" box and marks the recommended level. */
class LevelController extends Controller
{
    /** Answer in the finder → level number (by position on the path, so it still works with fewer or more levels). */
    public const FINDER = [1 => 1, 2 => 2, 3 => 3, 4 => 4];

    public function __invoke(Request $request): View
    {
        $levels = AiLevel::active()->with('course')->get();
        $from = (int) $request->query('from');
        $recommended = null;
        if (isset(self::FINDER[$from]) && $levels->isNotEmpty()) {
            $recommended = $levels->firstWhere('number', self::FINDER[$from]) ?? $levels->last();
        }

        return view('levels.index', compact('levels', 'recommended'));
    }
}
