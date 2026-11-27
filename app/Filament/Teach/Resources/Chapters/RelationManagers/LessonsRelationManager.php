<?php

namespace App\Filament\Teach\Resources\Chapters\RelationManagers;

use App\Filament\Resources\Chapters\RelationManagers\LessonsRelationManager as AdminLessons;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

/** Same lesson editor as admin, but the Bunny video ID is set by the Zirak team. */
class LessonsRelationManager extends AdminLessons
{
    public function form(Schema $schema): Schema
    {
        $schema = parent::form($schema);

        return $schema->components(collect($schema->getComponents())->map(function ($component) {
            if ($component instanceof TextInput && $component->getName() === 'video_id') {
                return $component->disabled()->dehydrated(false)
                    ->helperText(__('teach.video.use_button'));
            }

            return $component;
        })->all());
    }
}
