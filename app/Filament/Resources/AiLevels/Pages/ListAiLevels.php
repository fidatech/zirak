<?php

namespace App\Filament\Resources\AiLevels\Pages;

use App\Filament\Resources\AiLevels\AiLevelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAiLevels extends ListRecords
{
    protected static string $resource = AiLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
