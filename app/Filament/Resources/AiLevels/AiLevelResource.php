<?php

namespace App\Filament\Resources\AiLevels;

use App\Enums\CourseStatus;
use App\Filament\Resources\AiLevels\Pages\CreateAiLevel;
use App\Filament\Resources\AiLevels\Pages\EditAiLevel;
use App\Filament\Resources\AiLevels\Pages\ListAiLevels;
use App\Models\AiLevel;
use App\Models\Course;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** The AI path shown on the home page and /levels: Level 1 → 5, in three languages. */
class AiLevelResource extends Resource
{
    protected static ?string $model = AiLevel::class;

    protected static ?string $modelLabel = 'AI level';

    protected static ?string $pluralModelLabel = 'AI levels';

    protected static ?string $slug = 'ai-levels';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        $tab = fn (string $code, string $label, bool $required) => Tab::make($label)->schema([
            TextInput::make("content.$code.title")->label('Level name')->required($required)->maxLength(80),
            TextInput::make("content.$code.tagline")->label('Short line under the name')->maxLength(80),
            Textarea::make("content.$code.who")->label('Who it is for')->rows(2)->maxLength(300),
            Textarea::make("content.$code.learn")->label('What students learn (one per line, 3 is best)')->rows(4)->maxLength(800),
            Textarea::make("content.$code.after")->label('What they can do after this level')->rows(2)->maxLength(300),
            TextInput::make("content.$code.duration")->label('Length, as students should read it (e.g. 4 weeks)')->maxLength(40),
        ]);

        return $schema->components([
            Section::make('Level')->columnSpanFull()->columns(4)->schema([
                TextInput::make('number')->label('Level number')->numeric()->integer()->minValue(1)->maxValue(20)->required()->unique(ignoreRecord: true),
                Select::make('course_id')->label('Course for this level')->searchable()->preload()
                    ->options(fn () => Course::orderBy('title')->get()->mapWithKeys(fn (Course $c) => [$c->id => $c->title.($c->status === CourseStatus::Published ? '' : ' (not published)')]))
                    ->helperText('Students go to this course. Its price is shown on the level.'),
                TextInput::make('price_afn')->label('Price to show when no course is linked (AFN)')->numeric()->integer()->minValue(0)
                    ->helperText('0 = Free. Leave empty to hide the price.'),
                Toggle::make('is_active')->label('Show on the website')->default(true),
            ]),
            Tabs::make('Text')->columnSpanFull()->tabs([
                $tab('prs', 'Dari', true),
                $tab('ps', 'Pashto', false),
                $tab('en', 'English', false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('number')
            ->columns([
                TextColumn::make('number')->label('Level')->sortable(),
                TextColumn::make('title')->label('Name (English)')->getStateUsing(fn (AiLevel $r) => $r->content['en']['title'] ?? '—'),
                TextColumn::make('title_prs')->label('Name (Dari)')->getStateUsing(fn (AiLevel $r) => $r->content['prs']['title'] ?? '—'),
                TextColumn::make('course.title')->label('Course')->placeholder('Not linked yet')->limit(40),
                IconColumn::make('is_active')->label('Shown')->boolean(),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAiLevels::route('/'),
            'create' => CreateAiLevel::route('/create'),
            'edit' => EditAiLevel::route('/{record}/edit'),
        ];
    }
}
