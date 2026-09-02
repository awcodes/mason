<?php

declare(strict_types=1);

use Awcodes\Mason\BrickGroup;
use Awcodes\Mason\Bricks\Section;
use Awcodes\Mason\MasonEntry;
use Awcodes\Mason\Tests\Fixtures\TestBrick;

describe('MasonEntry', function () {
    it('creates entry with name', function () {
        $entry = MasonEntry::make('content');

        expect($entry)->toBeInstanceOf(MasonEntry::class);
    });

    it('has HasBricks trait functionality', function () {
        $entry = MasonEntry::make('content')
            ->bricks([TestBrick::class]);

        expect($entry->getBricks())->toBe([TestBrick::class]);
    });

    it('returns default Section brick when not set', function () {
        $entry = MasonEntry::make('content');

        expect($entry->getBricks())->toBe([Section::class]);
    });

    it('caches bricks by id', function () {
        $entry = MasonEntry::make('content')
            ->bricks([TestBrick::class]);

        $cached = $entry->getCachedBricks();

        expect($cached)->toHaveKey('test-brick')
            ->and($cached['test-brick'])->toBe(TestBrick::class);
    });

    it('gets brick by id', function () {
        $entry = MasonEntry::make('content')
            ->bricks([TestBrick::class]);

        expect($entry->getBrick('test-brick'))->toBe(TestBrick::class)
            ->and($entry->getBrick('unknown'))->toBeNull();
    });

    it('returns config default for preview layout by default', function () {
        $entry = MasonEntry::make('content');

        expect($entry->getPreviewLayout())->toBe(config('mason.entry.layout'));
    });

    it('can set preview layout', function () {
        $entry = MasonEntry::make('content')
            ->previewLayout('custom-layout');

        expect($entry->getPreviewLayout())->toBe('custom-layout');
    });

    it('can set preview layout using closure', function () {
        $entry = MasonEntry::make('content')
            ->previewLayout(fn () => 'dynamic-layout');

        expect($entry->getPreviewLayout())->toBe('dynamic-layout');
    });
});

describe('MasonEntry brick groups', function () {
    it('flattens BrickGroups for the entry view', function () {
        $entry = MasonEntry::make('content')
            ->bricks([
                BrickGroup::make('Content')->bricks([TestBrick::class]),
                Section::class,
            ]);

        // The entry view serialises getFlatBricks() into the Alpine payload and
        // posts it back to the entry endpoint. A BrickGroup reaching that payload
        // is fatal there, because it has no getId().
        expect($entry->getFlatBricks())->toBe([TestBrick::class, Section::class]);
    });
});
