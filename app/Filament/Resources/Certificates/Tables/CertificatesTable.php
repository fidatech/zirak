<?php

namespace App\Filament\Resources\Certificates\Tables;

use App\Models\Certificate;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CertificatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('issued_at', 'desc')
            ->columns([
                TextColumn::make('code')->searchable()->copyable(),
                TextColumn::make('name_on_certificate')->label('Name')->searchable(),
                TextColumn::make('course.title')->limit(40),
                TextColumn::make('score')->suffix('%'),
                TextColumn::make('status')->badge()->color(fn ($state) => $state === 'valid' ? 'success' : 'danger'),
                TextColumn::make('issued_at')->dateTime()->sortable(),
            ])
            ->filters([SelectFilter::make('status')->options(['valid' => 'Valid', 'revoked' => 'Revoked'])])
            ->recordActions([
                Action::make('open')->label('Verify page')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Certificate $record) => $record->verifyUrl())->openUrlInNewTab(),
                Action::make('revoke')->color('danger')->icon('heroicon-o-no-symbol')
                    ->visible(fn (Certificate $record) => $record->isValid())
                    ->schema([TextInput::make('reason')->required()->maxLength(255)])
                    ->requiresConfirmation()
                    ->action(fn (Certificate $record, array $data) => $record->update(['status' => 'revoked', 'revoked_reason' => $data['reason']])),
                Action::make('restore')->color('gray')
                    ->visible(fn (Certificate $record) => ! $record->isValid())
                    ->requiresConfirmation()
                    ->action(fn (Certificate $record) => $record->update(['status' => 'valid', 'revoked_reason' => null])),
            ]);
    }
}
