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

    public function getTitle(): string
    {
        return __('teach.profile.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('teach.nav.profile');
    }

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
            TextInput::make('name')->label(__('teach.profile.full_name'))->required()->maxLength(120),
            TextInput::make('nickname')->label(__('teach.profile.nickname'))->maxLength(60)
                ->helperText(__('teach.profile.nickname_help')),
            TextInput::make('teacher_headline')->label(__('teach.profile.headline'))->maxLength(120)->placeholder(__('teach.profile.headline_hint')),
            Textarea::make('teacher_bio')->label(__('teach.profile.bio'))->rows(6)->maxLength(2000),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])->id('form')->livewireSubmitHandler('save')
                ->footer([Actions::make([
                    Action::make('save')->label(__('teach.profile.save'))->submit('save'),
                    Action::make('view')->label(__('teach.profile.view'))->color('gray')
                        ->url(fn () => route('teachers.show', auth()->user()))->openUrlInNewTab(),
                ])]),
        ]);
    }

    public function save(): void
    {
        auth()->user()->update($this->form->getState());
        Notification::make()->title(__('teach.profile.saved'))->success()->send();
    }
}
