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
            Section::make('Course')->columns(2)->columnSpanFull()->schema([
                TextInput::make('title')->required()->maxLength(255)->columnSpanFull()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set, $get) => $get('slug') ? null : $set('slug', Str::slug($state) ?: Str::lower(Str::random(8)))),
                TextInput::make('slug')->required()->unique(ignoreRecord: true)->alphaDash()->helperText('Web address: /courses/{slug} — use Latin letters'),
                Select::make('category_id')->relationship('category', 'name_en')->required()->searchable()->preload()
                    ->createOptionForm(fn ($schema) => CategoryForm::configure($schema)),
                Select::make('language')->required()->default('prs')->options(['prs' => 'Dari', 'ps' => 'Pashto', 'en' => 'English']),
                Select::make('level')->required()->default('beginner')->options(collect(CourseLevel::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)])),
                TextInput::make('subtitle')->maxLength(500)->columnSpanFull(),
                TagsInput::make('outcomes')->label('What students will learn')->placeholder('Add an outcome and press Enter')->columnSpanFull(),
                MarkdownEditor::make('description')->columnSpanFull(),
            ]),
            Section::make('Price')->columns(3)->schema([
                Toggle::make('is_free')->label('Free course')->live(),
                TextInput::make('price_afn')->label('Price (AFN) — HesabPay')->numeric()->minValue(0)->default(0)->suffix('AFN')->required()
                    ->hidden(fn ($get) => $get('is_free'))->disabled($locked),
                TextInput::make('price_usd_cents')->label('Price (US cents) — Stripe, later')->numeric()->minValue(0)->default(0)->helperText('999 = $9.99')
                    ->hidden(fn ($get) => $get('is_free'))->disabled($locked),
            ])->description($forTeacher ? 'Prices are locked after publishing. Ask the Zirak team to change them.' : null),
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
