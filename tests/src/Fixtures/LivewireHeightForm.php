<?php

declare(strict_types=1);

namespace Awcodes\Mason\Tests\Fixtures;

use Awcodes\Mason\Mason;
use Awcodes\Mason\MasonEntry;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Workbench\App\Models\Page;

class LivewireHeightForm extends LivewireForm
{
    public ?string $minHeight = null;

    public ?string $maxHeight = null;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->model(Page::class)
            ->schema([
                Mason::make('content')
                    ->minHeight($this->minHeight)
                    ->maxHeight($this->maxHeight),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                MasonEntry::make('content')
                    ->state([])
                    ->minHeight($this->minHeight)
                    ->maxHeight($this->maxHeight),
            ]);
    }

    public function render(): View
    {
        return view('fixtures.height-form');
    }
}
