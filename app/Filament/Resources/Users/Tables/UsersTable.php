<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->searchable()->placeholder('—'),
                TextColumn::make('nickname')->placeholder('—'),
                TextColumn::make('phone')->searchable()->placeholder('—'),
                TextColumn::make('email')->searchable()->placeholder('—'),
                TextColumn::make('role')->badge(),
                TextColumn::make('enrollments_count')->counts('enrollments')->label('Courses'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([SelectFilter::make('role')->options(['student' => 'Student', 'instructor' => 'Instructor', 'admin' => 'Admin'])])
            ->recordActions([EditAction::make()]);
    }
}
