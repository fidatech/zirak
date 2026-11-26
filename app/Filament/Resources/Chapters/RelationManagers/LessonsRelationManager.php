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

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    protected static ?string $title = 'Learn — lessons';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
            MarkdownEditor::make('body')->label('Lesson text')->columnSpanFull(),
            Textarea::make('prompt')->label('Prompt to copy (optional)')->rows(3)->columnSpanFull(),
            Textarea::make('task')->label('"Your turn" task (optional)')->rows(2)->columnSpanFull(),
            TextInput::make('video_id')->label('Bunny Stream video ID')->helperText('Leave empty until the video is uploaded'),
            TextInput::make('duration_seconds')->label('Length (seconds)')->numeric()->minValue(0)->default(300),
            Toggle::make('is_preview')->label('Free preview (visible before buying)'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('duration_seconds')->label('Minutes')->formatStateUsing(fn ($state) => (string) max(1, (int) ceil($state / 60))),
                IconColumn::make('video_id')->label('Video')->boolean()->getStateUsing(fn ($record) => filled($record->video_id)),
                IconColumn::make('is_preview')->label('Preview')->boolean(),
            ])
            ->headerActions([CreateAction::make()->slideOver()])
            ->recordActions([EditAction::make()->slideOver(), DeleteAction::make()]);
    }
}
