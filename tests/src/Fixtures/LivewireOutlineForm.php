<?php

declare(strict_types=1);

namespace Awcodes\Mason\Tests\Fixtures;

use Awcodes\Mason\Mason;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Workbench\App\Models\Page;

class LivewireOutlineForm extends LivewireForm
{
    public bool $hasOutline = true;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->model(Page::class)
            ->schema([
                Mason::make('content')
                    ->outline($this->hasOutline),
            ]);
    }

    public function render(): View
    {
        return view('fixtures.outline-form');
    }
}
