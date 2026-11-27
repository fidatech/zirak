<?php

namespace App\Filament\Teach\Resources\StudentQuestions;

use App\Filament\Resources\StudentQuestions\StudentQuestionResource as AdminQuestions;
use App\Filament\Teach\Resources\StudentQuestions\Pages\ListStudentQuestions;
use App\Models\TutorMessage;
use Illuminate\Database\Eloquent\Builder;

/** Questions students sent to a teacher from the AI tutor, for this teacher's courses only. */
class StudentQuestionResource extends AdminQuestions
{
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('course', fn (Builder $q) => $q->where('teacher_id', auth()->id()));
    }

    public static function getNavigationBadge(): ?string
    {
        $count = TutorMessage::waitingForTeacher()->whereHas('course', fn (Builder $q) => $q->where('teacher_id', auth()->id()))->count();

        return $count ? (string) $count : null;
    }

    public static function getPages(): array
    {
        return ['index' => ListStudentQuestions::route('/')];
    }
}
