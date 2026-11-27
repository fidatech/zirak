<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Services\TeacherEarnings;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/** What Zirak owes each teacher right now. Shown on the Teacher payouts page. */
class TeacherBalances extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Owed to teachers';

    public function table(Table $table): Table
    {
        $money = fn (User $u, string $key) => app(TeacherEarnings::class)->summary($u)
            ->map(fn ($r, $c) => TeacherEarnings::money($r[$key], $c))->implode(' · ') ?: '—';

        return $table
            ->query(User::query()->where('role', 'instructor'))
            ->columns([
                TextColumn::make('name')->label('Teacher'),
                TextColumn::make('balance')->label('Owed now')->weight('bold')->getStateUsing(fn (User $u) => $money($u, 'balance')),
                TextColumn::make('earned')->label('Earned in total')->getStateUsing(fn (User $u) => $money($u, 'earned')),
                TextColumn::make('paid_out')->label('Paid out')->getStateUsing(fn (User $u) => $money($u, 'paid_out')),
            ])
            ->paginated([10, 25]);
    }
}
