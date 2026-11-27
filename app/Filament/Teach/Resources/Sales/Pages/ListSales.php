<?php

namespace App\Filament\Teach\Resources\Sales\Pages;

use App\Filament\Teach\Resources\Sales\SaleResource;
use App\Filament\Teach\Widgets\TeacherStats;
use Filament\Resources\Pages\ListRecords;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderWidgets(): array
    {
        return [TeacherStats::class];
    }
}
