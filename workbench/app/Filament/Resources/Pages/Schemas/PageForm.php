<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Resources\Pages\Schemas;

use Awcodes\Mason\Mason;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Workbench\App\Mason\BrickCollection;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Mason::make('content')
                    ->bricks(BrickCollection::make())
                    ->previewLayout('layouts.mason-preview')
                    ->doubleClickToEdit()
                    ->extraInputAttributes(['style' => 'min-height: 30rem;'])
                    ->columnSpanFull(),
            ]);
    }
}
