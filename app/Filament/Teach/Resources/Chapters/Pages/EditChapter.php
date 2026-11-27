<?php

namespace App\Filament\Teach\Resources\Chapters\Pages;

use App\Filament\Teach\Resources\Chapters\ChapterResource;
use App\Filament\Teach\Resources\Courses\CourseResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditChapter extends EditRecord
{
    protected static string $resource = ChapterResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            CourseResource::getUrl('index') => 'My courses',
            CourseResource::getUrl('edit', ['record' => $this->record->course]) => $this->record->course->title,
            'Chapter',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')->label('Back to course')->color('gray')->icon('heroicon-o-arrow-uturn-left')
                ->url(fn () => CourseResource::getUrl('edit', ['record' => $this->record->course])),
        ];
    }
}
