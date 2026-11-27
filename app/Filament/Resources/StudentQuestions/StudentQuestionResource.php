<?php

namespace App\Filament\Resources\StudentQuestions;

use App\Filament\Resources\StudentQuestions\Pages\ListStudentQuestions;
use App\Models\TutorMessage;
use App\Services\Tutor\TutorService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/** Questions students sent to the course teacher from the AI tutor. */
class StudentQuestionResource extends Resource
{
    protected static ?string $model = TutorMessage::class;

    public static function getModelLabel(): string
    {
        return __('teach.model.student_question');
    }

    public static function getPluralModelLabel(): string
    {
        return __('teach.model.student_questions');
    }

    public static function getNavigationLabel(): string
    {
        return __('teach.nav.questions');
    }

    protected static ?string $slug = 'student-questions';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'student')->with(['course', 'lesson', 'answers']);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = TutorMessage::waitingForTeacher()->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('content')->label(__('teach.sq.question'))->wrap()->limit(160)->searchable(),
                TextColumn::make('course.title')->label(__('teach.sq.course'))->limit(30),
                TextColumn::make('lesson.title')->label(__('teach.sq.lesson'))->limit(30)->placeholder('—'),
                TextColumn::make('ai_answer')->label(__('teach.sq.ai_answer'))->wrap()->limit(160)
                    ->getStateUsing(fn (TutorMessage $record) => $record->answers->firstWhere('role', 'ai')?->content),
                TextColumn::make('status')->label(__('teach.sq.status'))->badge()
                    ->getStateUsing(fn (TutorMessage $record) => $record->answered_at ? 'answered' : ($record->escalated_at ? 'waiting' : 'ai_only'))
                    ->formatStateUsing(fn ($state) => __('teach.sq.'.$state))
                    ->color(fn ($state) => match ($state) {
                        'waiting' => 'warning', 'answered' => 'success', default => 'gray'
                    }),
                TextColumn::make('locale')->label(__('teach.sq.lang'))->badge(),
                TextColumn::make('created_at')->label(__('teach.sq.asked'))->since()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('waiting')->label(__('teach.sq.filter_waiting'))->default(true)
                    ->queries(
                        true: fn (Builder $q) => $q->whereNotNull('escalated_at')->whereNull('answered_at'),
                        false: fn (Builder $q) => $q,
                        blank: fn (Builder $q) => $q,
                    ),
                SelectFilter::make('course')->label(__('teach.sq.course'))->relationship('course', 'title'),
            ])
            ->recordActions([
                Action::make('answer')->label(__('teach.sq.answer'))->icon('heroicon-o-pencil-square')->color('primary')
                    ->visible(fn (TutorMessage $record) => $record->answered_at === null)
                    ->modalDescription(fn (TutorMessage $record) => $record->content)
                    ->schema([
                        Textarea::make('answer')->label(__('teach.sq.answer_label'))->required()->rows(6)->maxLength(3000),
                    ])
                    ->action(function (TutorMessage $record, array $data) {
                        app(TutorService::class)->teacherAnswer($record, auth()->user(), $data['answer']);
                        Notification::make()->title(__('teach.sq.answer_sent'))->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudentQuestions::route('/'),
        ];
    }
}
