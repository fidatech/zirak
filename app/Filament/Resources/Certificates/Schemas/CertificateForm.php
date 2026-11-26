<?php

namespace App\Filament\Resources\Certificates\Schemas;

use App\Enums\CertificateType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CertificateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('course_id')
                    ->relationship('course', 'title')
                    ->required(),
                TextInput::make('name_on_certificate')
                    ->required(),
                Select::make('type')
                    ->options(CertificateType::class)
                    ->required(),
                TextInput::make('score')
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->default('valid'),
                TextInput::make('revoked_reason'),
                DateTimePicker::make('issued_at')
                    ->required(),
            ]);
    }
}
