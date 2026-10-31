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

class LivewireCommandsForm extends LivewireForm implements HasActions
{
    use InteractsWithActions;

    public bool $isAddable = true;

    public bool $isReorderable = true;

    public bool $isDeletable = true;

    public bool $hasOutline = false;

    public function mount(): void
    {
        $this->form->fill([
            'content' => [
                ['type' => 'masonBrick', 'attrs' => ['id' => 'simple-brick', 'config' => []]],
                ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => ['title' => 'Second']]],
            ],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->model(Page::class)
            ->schema([
                TextInput::make('title'),
                Mason::make('content')
                    ->bricks([SimpleBrick::class, TestBrick::class])
                    ->outline($this->hasOutline)
                    // A closure, as fields usually decide by the record or the user
                    ->addable(fn (): bool => $this->isAddable)
                    ->reorderable(fn (): bool => $this->isReorderable)
                    ->deletable(fn (): bool => $this->isDeletable),
            ]);
    }

    public function getContentField(): Mason
    {
        return $this->form->getComponent('content');
    }

    public function render(): View
    {
        return view('fixtures.outline-form');
    }
}
