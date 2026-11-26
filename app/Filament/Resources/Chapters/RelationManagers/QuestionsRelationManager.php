<?php

namespace App\Filament\Resources\Chapters\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $title = 'Practice & Test — questions';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('kind')->required()->default('practice')
                ->options(['practice' => 'Practice (instant feedback)', 'test' => 'Chapter test (scored)']),
            Textarea::make('body')->label('Question')->required()->rows(2)->columnSpanFull(),
            Repeater::make('options')->label('Answer options (in order)')
                ->simple(TextInput::make('text')->required())
                ->minItems(2)->maxItems(6)->defaultItems(4)->reorderable(false)->live()->columnSpanFull(),
            Select::make('correct_index')->label('Correct answer')->required()
                ->options(fn ($get) => collect(array_values($get('options') ?? []))->mapWithKeys(fn ($text, $i) => [$i => ($i + 1).'. '.($text ?: '…')])->all()),
            Textarea::make('explanation')->label('Explanation shown after answering')->rows(2)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('kind')->badge()->color(fn ($state) => ($state?->value ?? $state) === 'test' ? 'warning' : 'info'),
                TextColumn::make('body')->limit(80)->wrap(),
                TextColumn::make('options')->label('Options')->formatStateUsing(fn ($record) => count($record->options ?? [])),
            ])
            ->filters([SelectFilter::make('kind')->options(['practice' => 'Practice', 'test' => 'Test'])])
            ->headerActions([CreateAction::make()->slideOver()])
            ->recordActions([EditAction::make()->slideOver(), DeleteAction::make()]);
    }
}
