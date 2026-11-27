<?php

namespace App\Filament\Resources\Courses\Tables;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Services\TeacherService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')->searchable()->wrap(),
                TextColumn::make('teacher.name')->label('Teacher')->placeholder('—')->toggleable(),
                TextColumn::make('category.name_en')->label('Category')->sortable(),
                TextColumn::make('language')->badge(),
                TextColumn::make('level')->badge(),
                TextColumn::make('price_afn')->label('AFN')->numeric()->sortable(),
                IconColumn::make('is_free')->boolean()->label('Free'),
                TextColumn::make('status')->badge()->color(fn ($state) => match ($state?->value ?? $state) {
                    'published' => 'success', 'review' => 'info', 'draft' => 'warning', default => 'gray'
                }),
                TextColumn::make('enrollments_count')->counts('enrollments')->label('Students')->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')->relationship('category', 'name_en'),
                SelectFilter::make('status')->options(['draft' => 'Draft', 'review' => 'Waiting for review', 'published' => 'Published', 'archived' => 'Archived']),
                SelectFilter::make('language')->options(['prs' => 'Dari', 'ps' => 'Pashto', 'en' => 'English']),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('view')->label('Open')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => route('courses.show', $record))->openUrlInNewTab(),
                EditAction::make(),
                Action::make('approve')->label('Approve & publish')->icon('heroicon-o-check-badge')->color('success')
                    ->visible(fn (Course $record) => $record->status === CourseStatus::Review)
                    ->requiresConfirmation()
                    ->modalDescription('Open the course first and check its lessons and questions. Publishing makes it visible and buyable.')
                    ->action(function (Course $record) {
                        app(TeacherService::class)->approveCourse($record);
                        Notification::make()->title('Course published')->success()->send();
                    }),
                Action::make('changes')->label('Request changes')->icon('heroicon-o-arrow-uturn-left')->color('warning')
                    ->visible(fn (Course $record) => $record->status === CourseStatus::Review)
                    ->schema([Textarea::make('note')->label('What should the teacher change?')->required()->rows(5)->maxLength(2000)])
                    ->action(function (Course $record, array $data) {
                        app(TeacherService::class)->requestChanges($record, $data['note']);
                        Notification::make()->title('Sent back to the teacher')->success()->send();
                    }),
            ]);
    }
}
