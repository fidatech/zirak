<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use App\Filament\Resources\Chapters\ChapterResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ChaptersRelationManager extends RelationManager
{
    protected static string $relationship = 'chapters';

    /** Which panel's chapter page the "Lessons & questions" button opens. */
    protected static string $chapterResource = ChapterResource::class;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('teach.course.chapters');
    }

    public static function getModelLabel(): ?string
    {
        return __('teach.model.chapter');
    }

    public static function getPluralModelLabel(): ?string
    {
        return __('teach.model.chapters');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label(__('teach.chapter.title'))->required()->maxLength(255)->columnSpanFull(),
            Textarea::make('summary')->label(__('teach.chapter.summary'))->rows(2)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->reorderable('sort')
            ->defaultSort('sort')
            ->description(__('teach.chapter.help'))
            ->columns([
                TextColumn::make('title')->label(__('teach.chapter.title')),
                TextColumn::make('lessons_count')->counts('lessons')->label(__('teach.chapter.lessons')),
                TextColumn::make('practice_questions_count')->counts('practiceQuestions')->label(__('teach.chapter.practice'))
                    ->color(fn ($state) => $state ? null : 'warning')->formatStateUsing(fn ($state) => $state ? n($state) : __('teach.chapter.none')),
                TextColumn::make('test_questions_count')->counts('testQuestions')->label(__('teach.chapter.test'))
                    ->color(fn ($state) => $state ? null : 'warning')->formatStateUsing(fn ($state) => $state ? n($state) : __('teach.chapter.none')),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([
                Action::make('content')->label(__('teach.chapter.content'))->icon('heroicon-o-academic-cap')
                    ->url(fn ($record) => static::$chapterResource::getUrl('edit', ['record' => $record])),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
