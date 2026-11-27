<?php

namespace App\Filament\Teach\Resources\Sales;

use App\Enums\OrderStatus;
use App\Filament\Teach\Resources\Sales\Pages\ListSales;
use App\Models\Order;
use App\Services\TeacherEarnings;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/** Paid orders of the teacher's courses. Students appear by nickname or name only, never by phone. */
class SaleResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $modelLabel = 'sale';

    protected static ?string $navigationLabel = 'Sales';

    protected static ?string $slug = 'sales';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('teacher_id', auth()->id())->where('status', OrderStatus::Paid)->with(['course', 'user']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('paid_at', 'desc')
            ->columns([
                TextColumn::make('paid_at')->label('Date')->date()->sortable(),
                TextColumn::make('course.title')->label('Course')->limit(40),
                TextColumn::make('student')->label('Student')->getStateUsing(fn (Order $o) => $o->user?->publicName()),
                TextColumn::make('total')->label('Price paid')->formatStateUsing(fn (Order $o) => TeacherEarnings::money($o->total, $o->currency)),
                TextColumn::make('teacher_share')->label('Your share')->suffix('%'),
                TextColumn::make('teacher_amount')->label('You earn')->weight('bold')
                    ->formatStateUsing(fn (Order $o) => TeacherEarnings::money($o->teacher_amount, $o->currency)),
            ])
            ->filters([SelectFilter::make('course')->relationship('course', 'title', fn (Builder $query) => $query->where('teacher_id', auth()->id()))])
            ->emptyStateHeading('No sales yet');
    }

    public static function getPages(): array
    {
        return ['index' => ListSales::route('/')];
    }
}
