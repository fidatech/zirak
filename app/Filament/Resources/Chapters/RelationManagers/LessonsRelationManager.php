<?php

namespace App\Filament\Resources\Chapters\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    protected static ?string $title = 'Learn — lessons';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('teach.chapter.lessons');
    }

    public static function getModelLabel(): ?string
    {
        return __('teach.model.lesson');
    }

    public static function getPluralModelLabel(): ?string
    {
        return __('teach.model.lessons');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label(__('teach.lesson.title'))->required()->maxLength(255)->columnSpanFull(),
            MarkdownEditor::make('body')->label(__('teach.lesson.body'))->columnSpanFull(),
            Textarea::make('prompt')->label(__('teach.lesson.prompt'))->rows(3)->columnSpanFull(),
            Textarea::make('task')->label(__('teach.lesson.task'))->rows(2)->columnSpanFull(),
            TextInput::make('video_id')->label(__('teach.lesson.video_id'))->helperText(__('teach.lesson.video_id_help')),
            TextInput::make('duration_seconds')->label(__('teach.lesson.duration'))->numeric()->minValue(0)->default(300),
            Toggle::make('is_preview')->label(__('teach.lesson.is_preview')),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('title')->label(__('teach.lesson.title')),
                TextColumn::make('duration_seconds')->label(__('teach.lesson.minutes'))->formatStateUsing(fn ($state) => n(max(1, (int) ceil($state / 60)))),
                IconColumn::make('video_id')->label(__('teach.lesson.video'))->boolean()->getStateUsing(fn ($record) => filled($record->video_id)),
                IconColumn::make('is_preview')->label(__('teach.lesson.preview'))->boolean(),
            ])
            ->headerActions([CreateAction::make()->slideOver()])
            ->recordActions([EditAction::make()->slideOver(), DeleteAction::make()]);
    }
}
