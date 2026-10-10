<?php

declare(strict_types=1);

namespace Awcodes\Mason\Tests\Fixtures;

use Awcodes\Mason\Mason;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Workbench\App\Models\Page;

class LivewireNestedForm extends LivewireForm
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->model(Page::class)
            ->schema([
                TextInput::make('title'),
                Mason::make('content')
                    ->bricks([
                        SimpleBrick::class,
                        TestBrick::class,
                        ContainerBrick::class,
                        RestrictedContainerBrick::class,
                    ]),
            ]);
    }
}
