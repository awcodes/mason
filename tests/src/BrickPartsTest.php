<?php

declare(strict_types=1);

use Awcodes\Mason\Mason;
use Awcodes\Mason\Support\IframeEntryRenderer;
use Awcodes\Mason\Support\IframeRenderer;
use Awcodes\Mason\Tests\Fixtures\LivewirePartsForm;
use Awcodes\Mason\Tests\Fixtures\PartsBrick;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

function partsBrick(array $config = []): array
{
    return ['type' => 'masonBrick', 'attrs' => ['id' => 'parts-brick', 'config' => $config]];
}

function editPartsBrick(?string $part): TestAction
{
    return TestAction::make('handleBrick')
        ->schemaComponent('content')
        ->arguments(array_filter([
            'id' => 'parts-brick',
            'mode' => 'edit',
            'blockIndex' => 0,
            'config' => ['heading' => 'Hello', 'items' => [['title' => 'First'], ['title' => 'Second']]],
            'part' => $part,
        ]));
}

describe('rendering', function () {
    it('marks parts and reports the preview while the editor preview renders a brick', function () {
        $html = IframeRenderer::make([partsBrick()])->bricks([PartsBrick::class])->toHtml();

        expect($html)->toContain('<section data-mason-part="design" data-preview="yes">')
            ->toContain('<h2 data-mason-part="heading">');
    });

    it('outputs nothing outside the editor preview', function (string $renderer) {
        $html = match ($renderer) {
            'front end' => mason([partsBrick()], [PartsBrick::class])->toHtml(),
            'entry' => IframeEntryRenderer::make([partsBrick()])->bricks([PartsBrick::class])->toHtml(),
            'brick' => (string) PartsBrick::toHtml([]),
        };

        expect($html)->toContain('Heading')
            ->not->toContain('data-mason-part')
            ->not->toContain('data-preview="yes"');
    })->with(['front end', 'entry', 'brick']);

    it('escapes the part key', function () {
        $reflection = new ReflectionProperty(IframeRenderer::class, 'renderingPreview');
        $reflection->setValue(null, 1);

        try {
            expect(mason_part('a" onclick="x')->toHtml())->toBe('data-mason-part="a&quot; onclick=&quot;x"');
        } finally {
            $reflection->setValue(null, 0);
        }
    });

    it('ends the preview flag when a brick fails to render', function () {
        expect(fn () => IframeRenderer::make([partsBrick(['fail' => true])])->bricks([PartsBrick::class])->toHtml())
            ->toThrow(RuntimeException::class)
            ->and(Mason::isRenderingPreview())->toBeFalse();
    });
});

describe('opening the form at a part', function () {
    it('reveals the component with the part key', function (string $part, string $key) {
        $component = Livewire::test(LivewirePartsForm::class)
            ->mountAction(editPartsBrick($part))
            ->assertActionMounted(editPartsBrick($part));

        $component->assertDispatched(
            'mason-reveal-part',
            field: 'form.content',
            target: "{$component->id()}.mountedActionSchema0.{$key}",
        );
    })->with([
        'a field in the first tab' => ['heading', 'content.heading'],
        'a field in another tab' => ['color', 'design.color'],
        'a tab' => ['design', 'design'],
        'a field in a collapsed section' => ['anchor', 'design.advanced.anchor'],
    ]);

    it('reveals a repeater item, or a field in it, by its position', function (string $part, string $suffix) {
        $component = Livewire::test(LivewirePartsForm::class)->mountAction(editPartsBrick($part));

        $itemKey = array_keys($component->get('mountedActions.0.data.items'))[1];

        $component->assertDispatched(
            'mason-reveal-part',
            field: 'form.content',
            target: "{$component->id()}.mountedActionSchema0.content.items.{$itemKey}{$suffix}",
        );
    })->with([
        'a field in the second item' => ['items.1.title', '.title'],
        'the second item' => ['items.1', ''],
    ]);

    it('still reveals the part when the brick sets its own afterFormFilled()', function () {
        $called = false;
        PartsBrick::$afterFormFilled = function () use (&$called): void {
            $called = true;
        };

        try {
            $component = Livewire::test(LivewirePartsForm::class)->mountAction(editPartsBrick('color'));
        } finally {
            PartsBrick::$afterFormFilled = null;
        }

        expect($called)->toBeTrue();

        $component->assertDispatched(
            'mason-reveal-part',
            field: 'form.content',
            target: "{$component->id()}.mountedActionSchema0.design.color",
        );
    });

    it('opens the form as usual without a part, or one that is missing or hidden', function (?string $part) {
        Livewire::test(LivewirePartsForm::class)
            ->mountAction(editPartsBrick($part))
            ->assertActionMounted(editPartsBrick($part))
            ->assertNotDispatched('mason-reveal-part');
    })->with([
        'no part' => [null],
        'unknown key' => ['missing'],
        'hidden field' => ['secret'],
        'item beyond the last' => ['items.5.title'],
        'missing key in an item' => ['items.1.missing'],
    ]);
});
