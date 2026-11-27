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

    public static function getModelLabel(): string
    {
        return __('teach.model.payout');
    }

    public static function getPluralModelLabel(): string
    {
        return __('teach.model.payouts');
    }

    public static function getNavigationLabel(): string
    {
        return __('teach.nav.payouts');
    }

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
                TextColumn::make('paid_on')->label(__('teach.payouts.date'))->formatStateUsing(fn ($state) => n($state?->translatedFormat('j F Y'))),
                TextColumn::make('amount')->label(__('teach.payouts.amount'))->weight('bold')->formatStateUsing(fn (TeacherPayout $p) => TeacherEarnings::money($p->amount, $p->currency)),
                TextColumn::make('method')->label(__('teach.payouts.method'))->formatStateUsing(fn ($state) => __('teach.payouts.method_'.$state)),
                TextColumn::make('reference')->label(__('teach.payouts.reference'))->placeholder('—'),
                TextColumn::make('note')->label(__('teach.payouts.note'))->placeholder('—')->wrap(),
            ])
            ->emptyStateHeading(__('teach.payouts.empty'));
    }

    public static function getPages(): array
    {
        return ['index' => ListPayouts::route('/')];
    }
}
