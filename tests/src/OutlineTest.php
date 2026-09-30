<?php

declare(strict_types=1);

use Awcodes\Mason\Tests\Fixtures\LivewireOutlineForm;
use Livewire\Livewire;

it('does not render the outline when disabled', function () {
    Livewire::test(LivewireOutlineForm::class, ['hasOutline' => false])
        ->assertSeeHtml('mason-sidebar')
        ->assertDontSeeHtml('mason-outline-panel')
        ->assertDontSeeHtml('mason-sidebar-tabs');
});

it('renders the outline when enabled', function () {
    Livewire::test(LivewireOutlineForm::class)
        ->assertSeeHtml('mason-outline-panel')
        ->assertSeeHtml('mason-sidebar-tabs')
        ->assertSeeHtml('outlineBricks');
});
