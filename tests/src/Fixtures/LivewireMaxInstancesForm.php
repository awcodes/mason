<?php

declare(strict_types=1);

namespace Awcodes\Mason\Tests\Fixtures;

use Awcodes\Mason\Mason;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Workbench\App\Models\Page;

class LivewireMaxInstancesForm extends LivewireForm implements HasActions
{
    use InteractsWithActions;

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
                    ->bricks([LimitedBrick::class, SimpleBrick::class]),
            ]);
    }

    public function render(): View
    {
        return view('fixtures.outline-form');
    }

    public function getContentField(): Mason
    {
        return $this->form->getComponent('content');
    }
}
