<?php

namespace App\Filament\Teach\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/** What students see on the public teacher page. */
class TeacherProfile extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?string $navigationLabel = 'My public profile';

    protected static ?string $title = 'My public profile';

    protected static ?string $slug = 'profile';

    protected static ?int $navigationSort = 5;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(auth()->user()->only('name', 'nickname', 'teacher_headline', 'teacher_bio'));
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            TextInput::make('name')->label('Full name')->required()->maxLength(120),
            TextInput::make('nickname')->label('Name shown to students (optional)')->maxLength(60)
                ->helperText('If set, students see this instead of your full name. Your phone number is never shown.'),
            TextInput::make('teacher_headline')->label('Headline')->maxLength(120)->placeholder('e.g. Graphic designer, 8 years of experience'),
            Textarea::make('teacher_bio')->label('About you')->rows(6)->maxLength(2000),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])->id('form')->livewireSubmitHandler('save')
                ->footer([Actions::make([
                    Action::make('save')->label('Save')->submit('save'),
                    Action::make('view')->label('View my public page')->color('gray')
                        ->url(fn () => route('teachers.show', auth()->user()))->openUrlInNewTab(),
                ])]),
        ]);
    }

    public function save(): void
    {
        auth()->user()->update($this->form->getState());
        Notification::make()->title('Profile saved')->success()->send();
    }
}
