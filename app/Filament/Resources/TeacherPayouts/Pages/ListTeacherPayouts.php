<?php

namespace App\Filament\Resources\TeacherPayouts\Pages;

use App\Filament\Resources\TeacherPayouts\TeacherPayoutResource;
use App\Filament\Widgets\TeacherBalances;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTeacherPayouts extends ListRecords
{
    protected static string $resource = TeacherPayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Record a payout')
                ->mutateDataUsing(fn (array $data) => [...$data, 'created_by' => auth()->id()]),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [TeacherBalances::class];
    }
}
