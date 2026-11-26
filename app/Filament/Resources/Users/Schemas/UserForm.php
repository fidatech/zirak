<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->maxLength(120),
            TextInput::make('nickname')->maxLength(60),
            TextInput::make('phone')->tel()->unique(ignoreRecord: true)->helperText('International format, e.g. +93790000000'),
            TextInput::make('email')->email()->unique(ignoreRecord: true),
            Select::make('role')->required()->default('student')->options(['student' => 'Student', 'instructor' => 'Instructor', 'admin' => 'Admin']),
            Select::make('locale')->required()->default('prs')->options(['prs' => 'Dari', 'ps' => 'Pashto', 'en' => 'English']),
            TextInput::make('password')->password()->revealable()->dehydrated(fn ($state) => filled($state))
                ->helperText('Only needed for admins (email login at /admin). Leave empty to keep.'),
        ]);
    }
}
