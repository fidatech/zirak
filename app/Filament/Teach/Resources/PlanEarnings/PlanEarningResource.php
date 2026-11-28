<?php

namespace App\Filament\Teach\Resources\PlanEarnings;

use App\Filament\Teach\Resources\PlanEarnings\Pages\ListPlanEarnings;
use App\Models\TeacherPoolEarning;
use App\Services\TeacherEarnings;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/** The teacher's monthly share of Zirak Plus money. Read only, own rows only. */
class PlanEarningResource extends Resource
{
    protected static ?string $model = TeacherPoolEarning::class;

    protected static ?string $slug = 'plan-earnings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?int $navigationSort = 4;

    public static function getModelLabel(): string
    {
        return __('teach.model.plan_earning');
    }

    public static function getPluralModelLabel(): string
    {
        return __('teach.model.plan_earnings');
    }

    public static function getNavigationLabel(): string
    {
        return __('teach.nav.plan_earnings');
    }

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
            ->description(__('teach.plan.explain'))
            ->defaultSort('month', 'desc')
            ->columns([
                TextColumn::make('month')->label(__('teach.plan.month'))->sortable()
                    ->formatStateUsing(fn ($state) => n(CarbonImmutable::createFromFormat('Y-m-d', $state.'-01')->translatedFormat('F Y'))),
                TextColumn::make('lessons')->label(__('teach.plan.lessons'))->formatStateUsing(fn ($state) => n(number_format($state))),
                TextColumn::make('share')->label(__('teach.plan.share'))
                    ->getStateUsing(fn (TeacherPoolEarning $r) => $r->total_lessons ? pct((int) round($r->lessons * 100 / $r->total_lessons)) : pct(0)),
                TextColumn::make('pool')->label(__('teach.plan.pool'))->formatStateUsing(fn (TeacherPoolEarning $r) => TeacherEarnings::money($r->pool, $r->currency)),
                TextColumn::make('amount')->label(__('teach.plan.amount'))->weight('bold')->formatStateUsing(fn (TeacherPoolEarning $r) => TeacherEarnings::money($r->amount, $r->currency)),
            ])
            ->emptyStateHeading(__('teach.plan.empty'))
            ->emptyStateDescription(__('teach.plan.explain'));
    }

    public static function getPages(): array
    {
        return ['index' => ListPlanEarnings::route('/')];
    }
}
