<?php

namespace App\Filament\Teach\Resources\Payouts;

use App\Filament\Teach\Resources\Payouts\Pages\ListPayouts;
use App\Models\TeacherPayout;
use App\Services\TeacherEarnings;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PayoutResource extends Resource
{
    protected static ?string $model = TeacherPayout::class;

    protected static ?string $modelLabel = 'payout';

    protected static ?string $navigationLabel = 'Payouts';

    protected static ?string $slug = 'payouts';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('teacher_id', auth()->id());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('paid_on', 'desc')
            ->columns([
                TextColumn::make('paid_on')->label('Date')->date(),
                TextColumn::make('amount')->weight('bold')->formatStateUsing(fn (TeacherPayout $p) => TeacherEarnings::money($p->amount, $p->currency)),
                TextColumn::make('method')->formatStateUsing(fn ($state) => TeacherPayout::METHODS[$state] ?? $state),
                TextColumn::make('reference')->placeholder('—'),
                TextColumn::make('note')->placeholder('—')->wrap(),
            ])
            ->emptyStateHeading('No payouts yet');
    }

    public static function getPages(): array
    {
        return ['index' => ListPayouts::route('/')];
    }
}
