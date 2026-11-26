<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Names')->columns(3)->schema([
                TextInput::make('name_prs')->label('Name (Dari)')->required()->maxLength(100),
                TextInput::make('name_ps')->label('Name (Pashto)')->required()->maxLength(100),
                TextInput::make('name_en')->label('Name (English)')->required()->maxLength(100)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set, $get) => $get('slug') ? null : $set('slug', Str::slug($state))),
            ]),
            Section::make('Look & order')->columns(3)->schema([
                TextInput::make('slug')->required()->unique(ignoreRecord: true)->alphaDash()->helperText('Used in the address: /subjects/{slug}'),
                Select::make('color')->required()->default('teal')
                    ->options(array_combine(array_keys(Category::COLORS), array_map('ucfirst', array_keys(Category::COLORS)))),
                Select::make('icon')->required()->default('spark')
                    ->options(array_combine(Category::ICONS, array_map('ucfirst', Category::ICONS))),
                TextInput::make('sort')->numeric()->default(0)->required(),
                Toggle::make('is_active')->label('Visible to students')->default(true),
            ]),
        ]);
    }
}
