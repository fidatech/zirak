<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('name_prs')->label('Dari')->searchable(),
                TextColumn::make('name_ps')->label('Pashto')->searchable(),
                TextColumn::make('name_en')->label('English')->searchable(),
                TextColumn::make('courses_count')->counts('courses')->label('Courses'),
                TextColumn::make('color')->badge(),
                IconColumn::make('is_active')->label('Visible')->boolean(),
            ])
            ->recordActions([EditAction::make()]);
    }
}
