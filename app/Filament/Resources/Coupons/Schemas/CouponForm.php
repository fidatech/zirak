<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('code')->required()->unique(ignoreRecord: true)->maxLength(40)
                ->dehydrateStateUsing(fn ($state) => strtoupper(trim($state))),
            Select::make('type')->required()->default('percent')->options(['percent' => 'Percent off', 'fixed' => 'Fixed amount off (AFN)'])->live(),
            TextInput::make('value')->required()->numeric()->minValue(1)
                ->suffix(fn ($get) => $get('type') === 'fixed' ? 'AFN' : '%')
                ->maxValue(fn ($get) => $get('type') === 'fixed' ? null : 100),
            TextInput::make('max_uses')->numeric()->minValue(1)->helperText('Empty = unlimited'),
            Select::make('course_id')->relationship('course', 'title')->searchable()->preload()->helperText('Empty = any course'),
            DateTimePicker::make('expires_at'),
            Toggle::make('is_scholarship')->label('Scholarship code'),
            TextInput::make('reason')->label('Reason / note')->maxLength(255),
            Toggle::make('is_active')->default(true),
        ]);
    }
}
