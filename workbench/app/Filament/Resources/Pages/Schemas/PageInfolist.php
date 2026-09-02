<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Resources\Pages\Schemas;

use Awcodes\Mason\MasonEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Workbench\App\Mason\BrickCollection;

class PageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('title'),
                TextEntry::make('slug'),
                MasonEntry::make('content')
                    ->bricks(BrickCollection::make())
                    ->previewLayout('layouts.mason-entry')
                    ->extraInputAttributes(['style' => 'min-height: 40rem;'])
                    ->columnSpanFull(),
            ]);
    }
}
