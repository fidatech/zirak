<?php

namespace App\Filament\Resources\Courses\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')->searchable()->wrap(),
                TextColumn::make('category.name_en')->label('Category')->sortable(),
                TextColumn::make('language')->badge(),
                TextColumn::make('level')->badge(),
                TextColumn::make('price_afn')->label('AFN')->numeric()->sortable(),
                IconColumn::make('is_free')->boolean()->label('Free'),
                TextColumn::make('status')->badge()->color(fn ($state) => match ($state?->value ?? $state) {
                    'published' => 'success', 'draft' => 'warning', default => 'gray'
                }),
                TextColumn::make('enrollments_count')->counts('enrollments')->label('Students')->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')->relationship('category', 'name_en'),
                SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived']),
                SelectFilter::make('language')->options(['prs' => 'Dari', 'ps' => 'Pashto', 'en' => 'English']),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('view')->label('Open')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => route('courses.show', $record))->openUrlInNewTab(),
                EditAction::make(),
            ]);
    }
}
