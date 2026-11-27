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

class ChaptersRelationManager extends RelationManager
{
    protected static string $relationship = 'chapters';

    /** Which panel's chapter page the "Lessons & questions" button opens. */
    protected static string $chapterResource = ChapterResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
            Textarea::make('summary')->rows(2)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->reorderable('sort')
            ->defaultSort('sort')
            ->description('Each chapter has three steps: Learn (lessons), Practice (practice questions) and Test (test questions).')
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('lessons_count')->counts('lessons')->label('Lessons'),
                TextColumn::make('practice_questions_count')->counts('practiceQuestions')->label('Practice')
                    ->color(fn ($state) => $state ? null : 'warning')->formatStateUsing(fn ($state) => $state ?: 'none'),
                TextColumn::make('test_questions_count')->counts('testQuestions')->label('Test')
                    ->color(fn ($state) => $state ? null : 'warning')->formatStateUsing(fn ($state) => $state ?: 'none'),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([
                Action::make('content')->label('Lessons & questions')->icon('heroicon-o-academic-cap')
                    ->url(fn ($record) => static::$chapterResource::getUrl('edit', ['record' => $record])),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
