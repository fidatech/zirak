<?php

namespace App\Filament\Resources\StudentQuestions\Pages;

use App\Filament\Resources\StudentQuestions\StudentQuestionResource;
use Filament\Resources\Pages\ListRecords;

class ListStudentQuestions extends ListRecords
{
    protected static string $resource = StudentQuestionResource::class;
}
