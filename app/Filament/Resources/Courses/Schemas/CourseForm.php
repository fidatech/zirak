<?php

namespace App\Filament\Resources\Courses\Schemas;

use App\Enums\CertificateType;
use App\Enums\CourseLevel;
use App\Enums\CourseStatus;
use App\Filament\Resources\Categories\Schemas\CategoryForm;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CourseForm
{
    /** $forTeacher: the teacher panel hides publishing, teacher and share fields, and locks prices once published. */
    public static function configure(Schema $schema, bool $forTeacher = false): Schema
    {
        $locked = fn ($record) => $forTeacher && $record?->status === CourseStatus::Published;

        return $schema->components([
            Section::make(__('teach.course.section_course'))->columns(2)->columnSpanFull()->schema([
                TextInput::make('title')->label(__('teach.course.title'))->required()->maxLength(255)->columnSpanFull()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set, $get) => $get('slug') ? null : $set('slug', Str::slug($state) ?: Str::lower(Str::random(8)))),
                TextInput::make('slug')->label(__('teach.course.slug'))->required()->unique(ignoreRecord: true)->alphaDash()->helperText(__('teach.course.slug_help')),
                Select::make('category_id')->label(__('teach.course.category'))->relationship('category', 'name_en')->getOptionLabelFromRecordUsing(fn ($record) => $record->name())->required()->searchable()->preload()
                    ->createOptionForm(fn ($schema) => CategoryForm::configure($schema)),
                Select::make('language')->label(__('teach.course.language'))->required()->default('prs')->options(['prs' => 'دری', 'ps' => 'پښتو', 'en' => 'English']),
                Select::make('level')->label(__('teach.course.level'))->required()->default('beginner')->options(collect(CourseLevel::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])),
                TextInput::make('subtitle')->label(__('teach.course.subtitle'))->maxLength(500)->columnSpanFull(),
                TagsInput::make('outcomes')->label(__('teach.course.outcomes'))->placeholder(__('teach.course.outcomes_hint'))->columnSpanFull(),
                MarkdownEditor::make('description')->label(__('teach.course.description'))->columnSpanFull(),
            ]),
            Section::make(__('teach.course.section_price'))->columns(3)->schema([
                Toggle::make('is_free')->label(__('teach.course.is_free'))->live(),
                TextInput::make('price_afn')->label(__('teach.course.price_afn'))->numeric()->minValue(0)->default(0)->suffix('AFN')->required()
                    ->hidden(fn ($get) => $get('is_free'))->disabled($locked),
                TextInput::make('price_usd_cents')->label(__('teach.course.price_usd'))->numeric()->minValue(0)->default(0)->helperText(__('teach.course.price_usd_help'))
                    ->hidden(fn ($get) => $get('is_free'))->disabled($locked),
            ])->description($forTeacher ? __('teach.course.price_locked') : null),
            Section::make('Teacher, certificate & publishing')->columns(2)->hidden($forTeacher)->schema([
                Select::make('teacher_id')->label('Teacher account')->relationship('teacher', 'name', fn ($query) => $query->where('role', 'instructor'))
                    ->searchable()->preload()->helperText('The teacher can edit this course in their teacher area and earns a share of each sale.'),
                TextInput::make('teacher_share')->label('Teacher share')->numeric()->integer()->minValue(0)->maxValue(100)->suffix('%')
                    ->placeholder((string) config('zirak.teacher_share'))->helperText('Empty = the default share.'),
                TextInput::make('instructor_name')->label('Instructor name (no teacher account)')->maxLength(255),
                TextInput::make('instructor_bio')->label('Instructor bio (no teacher account)')->maxLength(255),
                Select::make('certificate_type')->required()->default('achievement')
                    ->options(collect(CertificateType::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)])),
                Select::make('status')->required()->default('draft')
                    ->options(collect(CourseStatus::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)])),
                Toggle::make('is_featured')->label('Show on the home page'),
                DateTimePicker::make('published_at'),
            ]),
        ]);
    }
}
