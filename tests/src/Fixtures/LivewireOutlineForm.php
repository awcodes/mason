<?php

declare(strict_types=1);

namespace Awcodes\Mason\Tests\Fixtures;

use Awcodes\Mason\Mason;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Workbench\App\Models\Page;

class LivewireOutlineForm extends LivewireForm
{
    public bool $hasOutline = true;

    public ?array $initialContent = null;

    public function mount(): void
    {
        $this->form->fill(['content' => $this->initialContent]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->model(Page::class)
            ->schema([
                TextInput::make('title'),
                Mason::make('content')
                    ->outline($this->hasOutline),
            ]);
    }

    public function render(): View
    {
        return view('fixtures.outline-form');
    }
}
