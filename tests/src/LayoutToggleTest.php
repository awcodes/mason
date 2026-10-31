<?php

declare(strict_types=1);

use Awcodes\Mason\Tests\Fixtures\LivewireLayoutToggleForm;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;

it('does not render the layout toggle when disabled', function () {
    Livewire::test(LivewireLayoutToggleForm::class, ['hasLayoutToggle' => false])
        ->assertSeeHtml('mason-sidebar')
        ->assertDontSeeHtml('mason-layout-toggle')
        ->assertSeeHtml('hasLayoutToggle: false');
});

it('renders the layout toggle in the toolbar when enabled', function (string $locale) {
    app()->setLocale($locale);

    Livewire::test(LivewireLayoutToggleForm::class)
        ->assertSeeHtml('mason-layout-toggle')
        ->assertSeeHtml('hasLayoutToggle: true')
        ->assertSee(__('mason::mason.controls.layout_toggle'));
})->with(['en', 'ar']);

it('labels the layout toggle with a custom label', function () {
    Livewire::test(LivewireLayoutToggleForm::class, ['layoutToggleLabel' => 'Show header and footer'])
        ->assertSee('Show header and footer')
        ->assertDontSee(__('mason::mason.controls.layout_toggle'));
});

it('hides the marked parts of the preview layout until the layout is shown', function () {
    $styles = Blade::render('@masonStyles');

    expect($styles)->toContain('html:not(.mason-show-layout) [data-mason-layout]')
        ->toContain('.mason-show-layout #mason-preview-container:has(> .mason-drop-zone-start)');
});
