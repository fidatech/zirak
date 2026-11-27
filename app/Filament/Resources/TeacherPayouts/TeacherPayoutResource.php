<?php

namespace App\Filament\Resources\TeacherPayouts;

use App\Filament\Resources\TeacherPayouts\Pages\ListTeacherPayouts;
use App\Models\TeacherPayout;
use App\Models\User;
use App\Services\TeacherEarnings;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/** Money paid out to teachers. Recording a payout lowers the teacher's balance; it does not send money. */
class TeacherPayoutResource extends Resource
{
    protected static ?string $model = TeacherPayout::class;

    protected static ?string $modelLabel = 'teacher payout';

    protected static ?string $navigationLabel = 'Teacher payouts';

    protected static ?string $slug = 'teacher-payouts';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('teacher_id')->label('Teacher')->required()->live()->searchable()->preload()
                ->relationship('teacher', 'name', fn ($query) => $query->where('role', 'instructor'))
                ->helperText(function ($get) {
                    $teacher = $get('teacher_id') ? User::find($get('teacher_id')) : null;
                    if (! $teacher) {
                        return null;
                    }
                    $rows = app(TeacherEarnings::class)->summary($teacher);

                    return 'Owed now: '.($rows->map(fn ($r, $c) => TeacherEarnings::money($r['balance'], $c))->implode(' · ') ?: 'nothing');
                }),
            Select::make('currency')->required()->default('AFN')->options(['AFN' => 'AFN', 'USD' => 'USD'])->live(),
            TextInput::make('amount')->required()->numeric()->integer()->minValue(1)
                ->helperText(fn ($get) => $get('currency') === 'USD' ? 'In US cents: 2500 = $25.00' : 'In afghanis'),
            Select::make('method')->required()->default('hesabpay')->options(TeacherPayout::METHODS),
            TextInput::make('reference')->label('Transaction / receipt number')->maxLength(120),
            TextInput::make('note')->maxLength(500),
            DatePicker::make('paid_on')->required()->default(now()),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('paid_on', 'desc')
            ->columns([
                TextColumn::make('paid_on')->date()->sortable(),
                TextColumn::make('teacher.name')->label('Teacher')->searchable(),
                TextColumn::make('amount')->weight('bold')->formatStateUsing(fn (TeacherPayout $p) => TeacherEarnings::money($p->amount, $p->currency)),
                TextColumn::make('method')->formatStateUsing(fn ($state) => TeacherPayout::METHODS[$state] ?? $state),
                TextColumn::make('reference')->placeholder('—'),
            ])
            ->filters([SelectFilter::make('teacher')->relationship('teacher', 'name', fn ($query) => $query->where('role', 'instructor'))]);
    }

    public static function getPages(): array
    {
        return ['index' => ListTeacherPayouts::route('/')];
    }
}
