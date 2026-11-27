<?php

namespace App\Filament\Teach\Resources\Courses\Pages;

use App\Enums\CourseStatus;
use App\Filament\Teach\Resources\Courses\CourseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        return [...$data,
            'teacher_id' => $user->id,
            'status' => CourseStatus::Draft,
            'instructor_name' => $user->publicName(),
            'certificate_type' => 'achievement',
        ];
    }
}
