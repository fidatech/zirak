<?php

namespace App\Filament\Teach\Resources\Payouts\Pages;

use App\Filament\Teach\Resources\Payouts\PayoutResource;
use App\Filament\Teach\Widgets\TeacherStats;
use Filament\Resources\Pages\ListRecords;

class ListPayouts extends ListRecords
{
    protected static string $resource = PayoutResource::class;

    protected function getHeaderWidgets(): array
    {
        return [TeacherStats::class];
    }
}
