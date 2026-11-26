<?php

namespace App\Filament\Resources\Coupons\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->copyable(),
                TextColumn::make('value')->formatStateUsing(fn ($state, $record) => $record->type === 'fixed' ? number_format($state).' AFN' : $state.'%'),
                TextColumn::make('used_count')->label('Used')->formatStateUsing(fn ($state, $record) => $state.($record->max_uses ? ' / '.$record->max_uses : '')),
                TextColumn::make('course.title')->placeholder('Any course')->limit(30),
                IconColumn::make('is_scholarship')->boolean()->label('Scholarship'),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('expires_at')->dateTime()->placeholder('—'),
            ])
            ->recordActions([EditAction::make()]);
    }
}
