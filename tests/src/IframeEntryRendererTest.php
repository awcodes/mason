<?php

declare(strict_types=1);

use Awcodes\Mason\BrickGroup;
use Awcodes\Mason\Support\IframeEntryRenderer;
use Awcodes\Mason\Tests\Fixtures\SimpleBrick;
use Awcodes\Mason\Tests\Fixtures\TestBrick;

beforeEach(function () {
    // Mock debugbar to prevent errors in tests
    if (! app()->bound('debugbar')) {
        app()->singleton('debugbar', function () {
            return new class
            {
                public function disable(): void {}
            };
        });
    }
});

describe('IframeEntryRenderer', function () {
    describe('make()', function () {
        it('creates instance with blocks', function () {
            $blocks = [
                ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => []]],
            ];
            $renderer = IframeEntryRenderer::make($blocks);

            expect($renderer)->toBeInstanceOf(IframeEntryRenderer::class)
                ->and($renderer->getBlocks())->toBe($blocks);
        });

        it('creates instance with empty blocks', function () {
            $renderer = IframeEntryRenderer::make([]);

            expect($renderer->getBlocks())->toBe([]);
        });
    });

    describe('setBlocks() and getBlocks()', function () {
        it('sets and gets blocks', function () {
            $blocks = [
                ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => []]],
            ];
            $renderer = IframeEntryRenderer::make([])
                ->setBlocks($blocks);

            expect($renderer->getBlocks())->toBe($blocks);
        });

        it('replaces existing blocks', function () {
            $originalBlocks = [
                ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => ['title' => 'Original']]],
            ];
            $newBlocks = [
                ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => ['title' => 'New']]],
            ];

            $renderer = IframeEntryRenderer::make($originalBlocks)
                ->setBlocks($newBlocks);

            expect($renderer->getBlocks())->toBe($newBlocks);
        });
    });

    describe('getBlockHtml()', function () {
        it('returns HTML for registered brick', function () {
            $block = ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => ['title' => 'Hello']]];
            $renderer = IframeEntryRenderer::make([$block])
                ->bricks([TestBrick::class]);

            $html = $renderer->getBlockHtml($block);

            expect($html)->toContain('Hello');
        });

        it('returns null for non-masonBrick type', function () {
            $block = ['type' => 'paragraph', 'content' => 'text'];
            $renderer = IframeEntryRenderer::make([$block])
                ->bricks([TestBrick::class]);

            expect($renderer->getBlockHtml($block))->toBeNull();
        });

        it('returns null for blank id', function () {
            $block = ['type' => 'masonBrick', 'attrs' => ['id' => '', 'config' => []]];
            $renderer = IframeEntryRenderer::make([$block])
                ->bricks([TestBrick::class]);

            expect($renderer->getBlockHtml($block))->toBeNull();
        });

        it('returns null for missing id', function () {
            $block = ['type' => 'masonBrick', 'attrs' => ['config' => []]];
            $renderer = IframeEntryRenderer::make([$block])
                ->bricks([TestBrick::class]);

            expect($renderer->getBlockHtml($block))->toBeNull();
        });

        it('returns unregistered brick view for unregistered brick', function () {
            $block = ['type' => 'masonBrick', 'attrs' => ['id' => 'unknown-brick', 'config' => []]];
            $renderer = IframeEntryRenderer::make([$block])
                ->bricks([TestBrick::class]);

            expect($renderer->getBlockHtml($block))->toContain('unknown-brick')
                ->and($renderer->getBlockHtml($block))->toContain('not registered');
        });

        it('uses empty config when not provided', function () {
            $block = ['type' => 'masonBrick', 'attrs' => ['id' => 'simple-brick']];
            $renderer = IframeEntryRenderer::make([$block])
                ->bricks([SimpleBrick::class]);

            $html = $renderer->getBlockHtml($block);

            expect($html)->toContain('simple-brick');
        });
    });

    describe('toHtml()', function () {
        it('renders full iframe document', function () {
            $blocks = [
                ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => ['title' => 'Hello']]],
            ];
            $renderer = IframeEntryRenderer::make($blocks)
                ->bricks([TestBrick::class]);

            $html = $renderer->toHtml();

            expect($html)->toBeString()
                ->and($html)->toContain('Hello');
        });

        it('renders multiple blocks', function () {
            $blocks = [
                ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => ['title' => 'First']]],
                ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => ['title' => 'Second']]],
            ];
            $renderer = IframeEntryRenderer::make($blocks)
                ->bricks([TestBrick::class]);

            $html = $renderer->toHtml();

            expect($html)->toContain('First')
                ->and($html)->toContain('Second');
        });

        it('renders empty state with message', function () {
            $renderer = IframeEntryRenderer::make([])
                ->bricks([TestBrick::class]);

            $html = $renderer->toHtml();

            expect($html)->toBeString()
                ->and($html)->toContain('mason-entry-empty');
        });

        it('does not include edit controls', function () {
            $blocks = [
                ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => []]],
            ];
            $renderer = IframeEntryRenderer::make($blocks)
                ->bricks([TestBrick::class]);

            $html = $renderer->toHtml();

            expect($html)->not->toContain('mason-block-controls')
                ->and($html)->not->toContain('data-action="edit"')
                ->and($html)->not->toContain('data-action="delete"')
                ->and($html)->not->toContain('mason-drop-zone');
        });

        it('uses mason-entry-block class', function () {
            $blocks = [
                ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => []]],
            ];
            $renderer = IframeEntryRenderer::make($blocks)
                ->bricks([TestBrick::class]);

            $html = $renderer->toHtml();

            expect($html)->toContain('mason-entry-block');
        });
    });
});

describe('IframeEntryRenderer with BrickGroups', function () {
    it('renders a brick registered inside a BrickGroup', function () {
        $renderer = IframeEntryRenderer::make([
            ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => ['title' => 'Grouped']]],
        ])->bricks([
            BrickGroup::make('Content')->bricks([TestBrick::class]),
        ]);

        $html = $renderer->getBlockHtml([
            'type' => 'masonBrick',
            'attrs' => ['id' => 'test-brick', 'config' => ['title' => 'Grouped']],
        ]);

        expect($html)->toContain('Grouped')
            ->and($html)->not->toContain('unregistered');
    });

    it('labels a brick registered inside a BrickGroup', function () {
        $renderer = IframeEntryRenderer::make([])->bricks([
            BrickGroup::make('Content')->bricks([TestBrick::class]),
        ]);

        $html = $renderer
            ->setBlocks([
                ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => []]],
            ])
            ->toHtml('fixtures.labels');

        expect($html)->toContain('data-label="Test Brick"');
    });
});
