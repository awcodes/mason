<?php

declare(strict_types=1);

namespace Awcodes\Mason\Tests\Fixtures;

use Awcodes\Mason\Mason;
use Closure;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Workbench\App\Models\Page;

class LivewireBrickActionForm extends LivewireForm implements HasActions
{
    use InteractsWithActions;

    public static ?Closure $modifyBrickActionUsing = null;

    public function mount(): void
    {
        $this->form->fill([
            'content' => [
                ['type' => 'masonBrick', 'attrs' => ['id' => 'configured-brick', 'config' => ['heading' => 'Hello']]],
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
                    ->bricks([ConfiguredBrick::class, SimpleBrick::class])
                    ->brickAction(static::$modifyBrickActionUsing),
            ]);
    }

    public function render(): View
    {
        return view('fixtures.outline-form');
    }
}
