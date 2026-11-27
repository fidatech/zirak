<?php

namespace App\Filament\Teach\Resources\Courses;

use App\Enums\CourseStatus;
use App\Filament\Resources\Courses\Schemas\CourseForm;
use App\Filament\Teach\Resources\Courses\Pages\CreateCourse;
use App\Filament\Teach\Resources\Courses\Pages\EditCourse;
use App\Filament\Teach\Resources\Courses\Pages\ListCourses;
use App\Filament\Teach\Resources\Courses\RelationManagers\ChaptersRelationManager;
use App\Models\Course;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/** A teacher's own courses. Every query is limited to courses where teacher_id = the logged-in teacher. */
class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationLabel = 'My courses';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('teacher_id', auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return CourseForm::configure($schema, forTeacher: true);
    }

    public static function statusLabel(CourseStatus $status): string
    {
        return match ($status) {
            CourseStatus::Draft => 'Draft',
            CourseStatus::Review => 'Waiting for review',
            CourseStatus::Published => 'Published',
            CourseStatus::Archived => 'Archived',
        };
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('title')->searchable()->wrap(),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn (CourseStatus $state) => static::statusLabel($state))
                    ->color(fn (CourseStatus $state) => match ($state) {
                        CourseStatus::Published => 'success', CourseStatus::Review => 'warning', default => 'gray'
                    }),
                TextColumn::make('chapters_count')->counts('chapters')->label('Chapters'),
                TextColumn::make('enrollments_count')->counts('enrollments')->label('Students'),
                TextColumn::make('updated_at')->label('Last change')->since(),
            ])
            ->recordActions([EditAction::make()])
            ->emptyStateHeading('No courses yet')
            ->emptyStateDescription('Create your first course, add chapters with lessons and questions, then send it to the Zirak team for review.');
    }

    public static function getRelations(): array
    {
        return [ChaptersRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourses::route('/'),
            'create' => CreateCourse::route('/create'),
            'edit' => EditCourse::route('/{record}/edit'),
        ];
    }
}
