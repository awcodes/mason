<?php

namespace Awcodes\Mason\Tests\Fixtures;

use Awcodes\Mason\Mason;
use Awcodes\Mason\Tests\Models\Page;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class LivewireForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?Page $record = null;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->model(Page::class)
            ->schema([
                TextInput::make('title'),
                Mason::make('content'),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->record->update($data);
    }

    public function create(): void
    {
        $data = $this->form->getState();
        $model = app($this->form->getModel());

        $model->create($data);
    }

    public function render(): View
    {
        return view('fixtures.form');
    }
}
