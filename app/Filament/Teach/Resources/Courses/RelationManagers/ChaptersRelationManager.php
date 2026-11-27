<?php

namespace App\Filament\Teach\Resources\Courses\RelationManagers;

use App\Filament\Resources\Courses\RelationManagers\ChaptersRelationManager as AdminChapters;
use App\Filament\Teach\Resources\Chapters\ChapterResource;

class ChaptersRelationManager extends AdminChapters
{
    protected static string $chapterResource = ChapterResource::class;
}
