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
use Illuminate\Database\Eloquent\Model;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $title = 'Practice & Test — questions';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return ucfirst(__('teach.model.questions'));
    }

    public static function getModelLabel(): ?string
    {
        return __('teach.model.question');
    }

    public static function getPluralModelLabel(): ?string
    {
        return __('teach.model.questions');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('kind')->label(__('teach.question.kind'))->required()->default('practice')
                ->options(['practice' => __('teach.question.kind_practice'), 'test' => __('teach.question.kind_test')]),
            Textarea::make('body')->label(__('teach.question.body'))->required()->rows(2)->columnSpanFull(),
            Repeater::make('options')->label(__('teach.question.options'))
                ->simple(TextInput::make('text')->label(__('teach.question.option'))->required())
                ->minItems(2)->maxItems(6)->defaultItems(4)->reorderable(false)->live()->columnSpanFull(),
            Select::make('correct_index')->label(__('teach.question.correct'))->required()
                ->options(fn ($get) => collect(array_values($get('options') ?? []))->mapWithKeys(fn ($text, $i) => [$i => n($i + 1).') '.($text ?: '…')])->all()),
            Textarea::make('explanation')->label(__('teach.question.explanation'))->rows(2)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('kind')->label(__('teach.question.kind'))->badge()->formatStateUsing(fn ($state) => __('teach.question.'.($state?->value ?? $state)))->color(fn ($state) => ($state?->value ?? $state) === 'test' ? 'warning' : 'info'),
                TextColumn::make('body')->label(__('teach.question.body'))->limit(80)->wrap(),
                TextColumn::make('options')->label(__('teach.question.options_count'))->formatStateUsing(fn ($record) => n(count($record->options ?? []))),
            ])
            ->filters([SelectFilter::make('kind')->label(__('teach.question.kind'))->options(['practice' => __('teach.question.practice'), 'test' => __('teach.question.test')])])
            ->headerActions([CreateAction::make()->slideOver()])
            ->recordActions([EditAction::make()->slideOver(), DeleteAction::make()]);
    }
}
