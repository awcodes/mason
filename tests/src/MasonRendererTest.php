<?php

declare(strict_types=1);

use Awcodes\Mason\Support\MasonRenderer;
use Awcodes\Mason\Tests\Fixtures\DataBrick;
use Awcodes\Mason\Tests\Fixtures\TestBrick;
use Workbench\App\Models\Page;

describe('MasonRenderer', function () {
    describe('make()', function () {
        it('creates instance with array content', function () {
            $content = [
                ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => []]],
            ];
            $renderer = MasonRenderer::make($content);

            expect($renderer)->toBeInstanceOf(MasonRenderer::class);
        });

        it('creates instance with empty array', function () {
            $renderer = MasonRenderer::make([]);

            expect($renderer)->toBeInstanceOf(MasonRenderer::class)
                ->and($renderer->toArray())->toBe([]);
        });

        it('creates instance with null content', function () {
            $renderer = MasonRenderer::make(null);

            expect($renderer)->toBeInstanceOf(MasonRenderer::class)
                ->and($renderer->toArray())->toBe([]);
        });

        it('creates instance with string content', function () {
            $content = json_encode([
                ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => []]],
            ]);
            $renderer = MasonRenderer::make($content);

            expect($renderer)->toBeInstanceOf(MasonRenderer::class)
                ->and($renderer->toArray())->toHaveCount(1);
        });

        it('creates instance with empty string', function () {
            $renderer = MasonRenderer::make('');

            expect($renderer)->toBeInstanceOf(MasonRenderer::class)
                ->and($renderer->toArray())->toBe([]);
        });

        it('unwraps content wrapper', function () {
            $content = [
                'content' => [
                    ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => []]],
                ],
            ];
            $renderer = MasonRenderer::make($content)->bricks([TestBrick::class]);

            expect($renderer->toArray())->toHaveCount(1);
        });
    });

    describe('toArray()', function () {
        it('returns empty array for empty array', function () {
            $renderer = MasonRenderer::make([]);

            expect($renderer->toArray())->toBe([]);
        });

        it('returns empty array for null content', function () {
            $renderer = MasonRenderer::make(null);

            expect($renderer->toArray())->toBe([]);
        });

        it('returns empty array for empty string', function () {
            $renderer = MasonRenderer::make('');

            expect($renderer->toArray())->toBe([]);
        });

        it('returns blocks from array content', function () {
            $content = [
                ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => []]],
                ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => []]],
            ];
            $renderer = MasonRenderer::make($content);

            expect($renderer->toArray())->toHaveCount(2);
        });

        it('parses JSON string content', function () {
            $content = json_encode([
                ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => []]],
            ]);
            $renderer = MasonRenderer::make($content);

            expect($renderer->toArray())->toHaveCount(1);
        });

        it('returns empty array for invalid JSON', function () {
            $renderer = MasonRenderer::make('invalid json{');

            expect($renderer->toArray())->toBe([]);
        });
    });

    describe('data()', function () {
        it('passes data to the brick', function () {
            $page = Page::factory()->create(['title' => 'From The Record']);
            $content = [
                ['type' => 'masonBrick', 'attrs' => ['id' => 'data-brick', 'config' => []]],
            ];

            $html = MasonRenderer::make($content)
                ->bricks([DataBrick::class])
                ->data(['record' => $page])
                ->toHtml();

            expect($html)->toContain('From The Record');
        });

        it('accepts a closure', function () {
            $page = Page::factory()->create(['title' => 'Lazy Record']);
            $content = [
                ['type' => 'masonBrick', 'attrs' => ['id' => 'data-brick', 'config' => []]],
            ];

            $html = MasonRenderer::make($content)
                ->bricks([DataBrick::class])
                ->data(fn (): array => ['record' => $page])
                ->toHtml();

            expect($html)->toContain('Lazy Record');
        });

        it('hands the brick an empty array when no data is set', function () {
            $content = [
                ['type' => 'masonBrick', 'attrs' => ['id' => 'data-brick', 'config' => []]],
            ];

            $html = MasonRenderer::make($content)
                ->bricks([DataBrick::class])
                ->toHtml();

            expect($html)->toContain('no record');
        });
    });

    describe('toHtml()', function () {
        it('renders bricks to HTML', function () {
            $content = [
                ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => ['title' => 'Hello', 'content' => 'World']]],
            ];
            $renderer = MasonRenderer::make($content)->bricks([TestBrick::class]);

            $html = $renderer->toHtml();

            expect($html)->toContain('Hello')
                ->and($html)->toContain('World');
        });

        it('returns empty string for empty content', function () {
            $renderer = MasonRenderer::make([])->bricks([TestBrick::class]);

            expect($renderer->toHtml())->toBe('');
        });

        it('skips non-masonBrick types', function () {
            $content = [
                ['type' => 'paragraph', 'content' => 'text'],
                ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => ['title' => 'Hello']]],
            ];
            $renderer = MasonRenderer::make($content)->bricks([TestBrick::class]);

            $html = $renderer->toHtml();

            expect($html)->toContain('Hello')
                ->and($html)->not->toContain('paragraph');
        });

        it('skips bricks with blank id', function () {
            $content = [
                ['type' => 'masonBrick', 'attrs' => ['id' => '', 'config' => []]],
                ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => ['title' => 'Valid']]],
            ];
            $renderer = MasonRenderer::make($content)->bricks([TestBrick::class]);

            $html = $renderer->toHtml();

            expect($html)->toContain('Valid');
        });

        it('silently ignores unregistered bricks', function () {
            $content = [
                ['type' => 'masonBrick', 'attrs' => ['id' => 'unknown-brick', 'config' => []]],
            ];
            $renderer = MasonRenderer::make($content)->bricks([TestBrick::class]);

            expect($renderer->toHtml())->toBe('');
        });
    });

    describe('toUnsafeHtml()', function () {
        it('renders HTML without sanitization', function () {
            $content = [
                ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => ['title' => '<script>alert(1)</script>']]],
            ];
            $renderer = MasonRenderer::make($content)->bricks([TestBrick::class]);

            $html = $renderer->toUnsafeHtml();

            expect($html)->toContain('<script>');
        });
    });

    describe('toText()', function () {
        it('strips HTML tags', function () {
            $content = [
                ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => ['title' => 'Hello', 'content' => 'World']]],
            ];
            $renderer = MasonRenderer::make($content)->bricks([TestBrick::class]);

            $text = $renderer->toText();

            expect($text)->toContain('Hello')
                ->and($text)->toContain('World')
                ->and($text)->not->toContain('<div')
                ->and($text)->not->toContain('<h2');
        });

        it('returns empty string for empty content', function () {
            $renderer = MasonRenderer::make([])->bricks([TestBrick::class]);

            expect($renderer->toText())->toBe('');
        });
    });

    describe('getBrickHtml()', function () {
        it('returns HTML for registered brick', function () {
            $renderer = MasonRenderer::make([])->bricks([TestBrick::class]);

            $html = $renderer->getBrickHtml('test-brick', ['title' => 'Test']);

            expect($html)->toContain('Test');
        });

        it('returns empty string for unregistered brick', function () {
            $renderer = MasonRenderer::make([])->bricks([TestBrick::class]);

            expect($renderer->getBrickHtml('unknown-brick', []))->toBe('');
        });
    });

    describe('content()', function () {
        it('can set content fluently', function () {
            $content = [
                ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => ['title' => 'Set Later']]],
            ];
            $renderer = MasonRenderer::make([])
                ->bricks([TestBrick::class])
                ->content($content);

            expect($renderer->toHtml())->toContain('Set Later');
        });
    });
});
