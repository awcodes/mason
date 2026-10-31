<?php

declare(strict_types=1);

use Awcodes\Mason\Mason;
use Awcodes\Mason\MasonEntry;
use Awcodes\Mason\Tests\Fixtures\LivewireHeightForm;
use Livewire\Livewire;

describe('minHeight() and maxHeight()', function () {
    it('sets the heights on the field', function () {
        $component = Mason::make('content')
            ->minHeight('10rem')
            ->maxHeight(fn () => '60rem');

        expect($component->getMinHeight())->toBe('10rem')
            ->and($component->getMaxHeight())->toBe('60rem')
            ->and($component->getHeightStyles())->toBe([
                '--mason-min-height: 10rem',
                '--mason-max-height: 60rem',
            ]);
    });

    it('sets the minimum height on the entry', function () {
        $component = MasonEntry::make('content')->minHeight(fn () => '10rem');

        expect($component->getMinHeight())->toBe('10rem')
            ->and($component->getHeightStyles())->toBe(['--mason-min-height: 10rem']);
    });

    it('has no maximum height on the entry, whose iframe keeps its height', function () {
        expect(method_exists(MasonEntry::class, 'maxHeight'))->toBeFalse();
    });

    it('leaves the stylesheet defaults in place when not set', function (string $class) {
        expect($class::make('content')->getHeightStyles())->toBe([]);
    })->with(['field' => Mason::class, 'entry' => MasonEntry::class]);

    it('sets only the given height', function () {
        expect(Mason::make('content')->maxHeight('none')->getHeightStyles())
            ->toBe(['--mason-max-height: none']);
    });
});

it('renders the heights on the field and the minimum height on the entry', function () {
    $html = Livewire::test(LivewireHeightForm::class, ['minHeight' => '12rem', 'maxHeight' => '70rem'])->html();

    expect(substr_count($html, '--mason-min-height: 12rem; --mason-max-height: 70rem'))->toBe(1)
        ->and(substr_count($html, '--mason-min-height: 12rem'))->toBe(2);
});

it('renders no heights when none are set', function () {
    Livewire::test(LivewireHeightForm::class)
        ->assertDontSeeHtml('--mason-min-height')
        ->assertDontSeeHtml('--mason-max-height');
});
