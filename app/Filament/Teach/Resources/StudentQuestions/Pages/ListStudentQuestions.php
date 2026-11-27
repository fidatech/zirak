<?php

namespace App\Filament\Teach\Resources\StudentQuestions\Pages;

use App\Filament\Teach\Resources\StudentQuestions\StudentQuestionResource;
use Filament\Resources\Pages\ListRecords;

class ListStudentQuestions extends ListRecords
{
    protected static string $resource = StudentQuestionResource::class;
}
