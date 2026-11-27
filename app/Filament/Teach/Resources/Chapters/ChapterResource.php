<?php

namespace App\Filament\Teach\Resources\Chapters;

use App\Filament\Resources\Chapters\RelationManagers\QuestionsRelationManager;
use App\Filament\Teach\Resources\Chapters\Pages\EditChapter;
use App\Filament\Teach\Resources\Chapters\RelationManagers\LessonsRelationManager;
use App\Models\Chapter;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

/** Chapters of the teacher's own courses only (opened from a course's chapter list). */
class ChapterResource extends Resource
{
    protected static ?string $model = Chapter::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('course', fn (Builder $q) => $q->where('teacher_id', auth()->id()));
    }

    public static function form(Schema $schema): Schema
    {
        // No course picker: a chapter can't be moved to another course from here.
        return $schema->components([
            TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
            Textarea::make('summary')->rows(2)->columnSpanFull(),
        ]);
    }

    public static function getRelations(): array
    {
        return [LessonsRelationManager::class, QuestionsRelationManager::class];
    }

    public static function getPages(): array
    {
        return ['edit' => EditChapter::route('/{record}/edit')];
    }
}
