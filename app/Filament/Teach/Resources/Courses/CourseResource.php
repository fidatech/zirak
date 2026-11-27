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

    public static function getModelLabel(): string
    {
        return __('teach.model.course');
    }

    public static function getPluralModelLabel(): string
    {
        return __('teach.model.courses');
    }

    public static function getNavigationLabel(): string
    {
        return __('teach.nav.courses');
    }

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
        return __('teach.status.'.$status->value);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('title')->label(__('teach.course.title'))->searchable()->wrap(),
                TextColumn::make('status')->label(__('teach.course.status'))->badge()
                    ->formatStateUsing(fn (CourseStatus $state) => static::statusLabel($state))
                    ->color(fn (CourseStatus $state) => match ($state) {
                        CourseStatus::Published => 'success', CourseStatus::Review => 'warning', default => 'gray'
                    }),
                TextColumn::make('chapters_count')->counts('chapters')->label(__('teach.course.chapters'))->formatStateUsing(fn ($state) => n($state)),
                TextColumn::make('enrollments_count')->counts('enrollments')->label(__('teach.course.students'))->formatStateUsing(fn ($state) => n($state)),
                TextColumn::make('updated_at')->label(__('teach.course.last_change'))->since(),
            ])
            ->recordActions([EditAction::make()])
            ->emptyStateHeading(__('teach.course.empty_heading'))
            ->emptyStateDescription(__('teach.course.empty_text'));
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
