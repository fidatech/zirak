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

    public static function getModelLabel(): string
    {
        return __('teach.model.sale');
    }

    public static function getPluralModelLabel(): string
    {
        return __('teach.model.sales');
    }

    public static function getNavigationLabel(): string
    {
        return __('teach.nav.sales');
    }

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
                TextColumn::make('paid_at')->label(__('teach.sales.date'))->formatStateUsing(fn ($state) => n($state?->translatedFormat('j F Y')))->sortable(),
                TextColumn::make('course.title')->label(__('teach.sales.course'))->limit(40),
                TextColumn::make('student')->label(__('teach.sales.student'))->getStateUsing(fn (Order $o) => $o->user?->publicName()),
                TextColumn::make('total')->label(__('teach.sales.price'))->formatStateUsing(fn (Order $o) => TeacherEarnings::money($o->total, $o->currency)),
                TextColumn::make('teacher_share')->label(__('teach.sales.share'))->formatStateUsing(fn ($state) => pct($state)),
                TextColumn::make('teacher_amount')->label(__('teach.sales.earn'))->weight('bold')
                    ->formatStateUsing(fn (Order $o) => TeacherEarnings::money($o->teacher_amount, $o->currency)),
            ])
            ->filters([SelectFilter::make('course')->label(__('teach.sales.course'))->relationship('course', 'title', fn (Builder $query) => $query->where('teacher_id', auth()->id()))])
            ->emptyStateHeading(__('teach.sales.empty'));
    }

    public static function getPages(): array
    {
        return ['index' => ListSales::route('/')];
    }
}
