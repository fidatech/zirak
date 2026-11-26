<?php

namespace App\Filament\Resources\Chapters\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ChapterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('course_id')->relationship('course', 'title')->required()->searchable()->preload(),
            TextInput::make('title')->required()->maxLength(255),
            Textarea::make('summary')->rows(2)->columnSpanFull(),
        ]);
    }
}
