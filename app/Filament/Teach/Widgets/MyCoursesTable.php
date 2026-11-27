<?php

namespace App\Filament\Teach\Widgets;

use App\Enums\CourseStatus;
use App\Enums\OrderStatus;
use App\Filament\Teach\Resources\Courses\CourseResource;
use App\Models\Course;
use App\Services\TeacherEarnings;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/** The teacher's courses with students and earnings. Only their own courses. */
class MyCoursesTable extends TableWidget
{
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('teach.dash.my_courses'))
            ->query(Course::query()->where('teacher_id', auth()->id())
                ->withCount('enrollments')
                ->withSum(['orders as earned' => fn ($q) => $q->where('status', OrderStatus::Paid)->where('teacher_id', auth()->id())->where('currency', 'AFN')], 'teacher_amount'))
            ->defaultSort('enrollments_count', 'desc')
            ->columns([
                TextColumn::make('title')->label(__('teach.course.title'))->limit(40)->weight('bold'),
                TextColumn::make('status')->label(__('teach.course.status'))->badge()
                    ->formatStateUsing(fn (CourseStatus $state) => CourseResource::statusLabel($state))
                    ->color(fn (CourseStatus $state) => match ($state) {
                        CourseStatus::Published => 'success', CourseStatus::Review => 'warning', default => 'gray'
                    }),
                TextColumn::make('enrollments_count')->label(__('teach.course.students'))->formatStateUsing(fn ($state) => n($state))->sortable(),
                TextColumn::make('earned')->label(__('teach.dash.col_earned'))->default(0)->formatStateUsing(fn ($state) => TeacherEarnings::money((int) $state, 'AFN')),
            ])
            ->recordUrl(fn (Course $record) => CourseResource::getUrl('edit', ['record' => $record]))
            ->paginated(false)
            ->emptyStateHeading(__('teach.course.empty_heading'));
    }
}
