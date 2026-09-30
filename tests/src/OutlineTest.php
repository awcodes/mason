<?php

declare(strict_types=1);

use Awcodes\Mason\Tests\Fixtures\LivewireOutlineForm;
use Livewire\Livewire;
use Workbench\App\Models\Page;

it('does not render the outline when disabled', function () {
    Livewire::test(LivewireOutlineForm::class, ['hasOutline' => false])
        ->assertSeeHtml('mason-sidebar')
        ->assertDontSeeHtml('mason-outline-panel')
        ->assertDontSeeHtml('mason-outline-sheet')
        ->assertDontSeeHtml('mason-sidebar-tabs');
});

it('renders the outline when enabled', function () {
    Livewire::test(LivewireOutlineForm::class)
        ->assertSeeHtml('mason-outline-panel')
        ->assertSeeHtml('mason-sidebar-tabs')
        ->assertSeeHtml('mason-outline-sheet')
        ->assertSeeHtml('outlineBricks');
});

function sectionBrick(string $text): array
{
    return [
        'type' => 'masonBrick',
        'attrs' => [
            'id' => 'section',
            'config' => ['text' => $text],
        ],
    ];
}

it('labels each brick in the editor state when the outline is enabled', function () {
    $component = Livewire::test(LivewireOutlineForm::class, [
        'initialContent' => [sectionBrick('<p>About us</p>'), sectionBrick('<p>Contact</p>')],
    ]);

    expect(data_get($component->get('data'), 'content.*.attrs.outlineLabel'))
        ->toBe(['About us', 'Contact']);
});

it('does not label bricks when the outline is disabled', function () {
    $component = Livewire::test(LivewireOutlineForm::class, [
        'hasOutline' => false,
        'initialContent' => [sectionBrick('<p>About us</p>')],
    ]);

    expect($component->get('data.content.0.attrs'))
        ->toHaveKey('config')
        ->not->toHaveKey('outlineLabel');
});

it('does not save outline labels', function () {
    Livewire::test(LivewireOutlineForm::class, [
        'initialContent' => [sectionBrick('<p>About us</p>')],
    ])
        ->assertSet('data.content.0.attrs.outlineLabel', 'About us')
        ->fillForm(['title' => 'Outline'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Page::query()->first()->content)
        ->toBe([sectionBrick('<p>About us</p>')]);
});
