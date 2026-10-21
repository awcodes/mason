<?php

declare(strict_types=1);

namespace Awcodes\Mason\Tests\Fixtures;

use Awcodes\Mason\Bricks\Section;
use Awcodes\Mason\Mason;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Schema;
use Workbench\App\Models\Page;

class LivewireDuplicateForm extends LivewireForm implements HasActions
{
    use InteractsWithActions;

    public bool $isDisabled = false;

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
                Mason::make('content')
                    ->bricks([Section::class, AnchorBrick::class])
                    ->outline()
                    ->disabled($this->isDisabled),
            ]);
    }
}
