<?php

namespace App\Filament\Resources\TeacherApplications;

use App\Filament\Resources\TeacherApplications\Pages\ListTeacherApplications;
use App\Models\TeacherApplication;
use App\Services\TeacherService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TeacherApplicationResource extends Resource
{
    protected static ?string $model = TeacherApplication::class;

    protected static ?string $modelLabel = 'teacher application';

    protected static ?string $navigationLabel = 'Teacher applications';

    protected static ?string $slug = 'teacher-applications';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = TeacherApplication::where('status', 'pending')->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('full_name'),
            TextEntry::make('headline'),
            TextEntry::make('languages')->badge()->formatStateUsing(fn ($state) => ['prs' => 'Dari', 'ps' => 'Pashto', 'en' => 'English'][$state] ?? $state),
            TextEntry::make('sample_url')->label('Sample work')->url(fn ($state) => $state)->openUrlInNewTab()->placeholder('—'),
            TextEntry::make('about')->label('About')->columnSpanFull(),
            TextEntry::make('course_idea')->label('First course idea')->columnSpanFull(),
            TextEntry::make('admin_note')->label('Note to the applicant')->placeholder('—')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('full_name')->label('Name')->searchable(),
                TextColumn::make('headline')->limit(50)->wrap(),
                TextColumn::make('languages')->badge(),
                TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'pending' => 'warning', 'approved' => 'success', default => 'gray'
                }),
                TextColumn::make('created_at')->label('Sent')->since(),
            ])
            ->filters([SelectFilter::make('status')->default('pending')->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])])
            ->recordActions([
                Action::make('view')->label('Read')->icon('heroicon-o-eye')->color('gray')
                    ->schema(fn (Schema $schema) => static::infolist($schema))->modalSubmitAction(false)->modalCancelActionLabel('Close'),
                Action::make('approve')->label('Approve')->icon('heroicon-o-check')->color('success')
                    ->visible(fn (TeacherApplication $record) => $record->isPending())
                    ->requiresConfirmation()->modalDescription('They get the teacher area at /teach and can start building courses. Courses still need your review before they are published.')
                    ->action(function (TeacherApplication $record) {
                        app(TeacherService::class)->approveApplication($record, auth()->user());
                        Notification::make()->title('Teacher approved')->success()->send();
                    }),
                Action::make('reject')->label('Reject')->icon('heroicon-o-x-mark')->color('danger')
                    ->visible(fn (TeacherApplication $record) => $record->isPending())
                    ->schema([Textarea::make('note')->label('Note to the applicant (they see it)')->rows(4)->maxLength(1000)])
                    ->action(function (TeacherApplication $record, array $data) {
                        app(TeacherService::class)->rejectApplication($record, auth()->user(), $data['note'] ?? null);
                        Notification::make()->title('Application rejected')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListTeacherApplications::route('/')];
    }
}
