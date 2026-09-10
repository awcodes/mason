<?php

declare(strict_types=1);

use Awcodes\Mason\Bricks\Section;
use Awcodes\Mason\Support\MasonRenderer;
use Awcodes\Mason\Tests\Fixtures\DataBrick;
use Awcodes\Mason\Tests\Fixtures\TestBrick;
use Illuminate\Support\Facades\Blade;
use Workbench\App\Models\Page;

describe('mason() helper function', function () {
    it('returns MasonRenderer instance', function () {
        $renderer = mason([]);

        expect($renderer)->toBeInstanceOf(MasonRenderer::class);
    });

    it('accepts array content', function () {
        $content = [
            ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => ['title' => 'Test']]],
        ];
        $renderer = mason($content, [TestBrick::class]);

        expect($renderer->toArray())->toHaveCount(1);
    });

    it('accepts string content', function () {
        $content = json_encode([
            ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => []]],
        ]);
        $renderer = mason($content, [TestBrick::class]);

        expect($renderer->toArray())->toHaveCount(1);
    });

    it('accepts null content', function () {
        $renderer = mason(null, [TestBrick::class]);

        expect($renderer->toArray())->toBe([]);
    });

    it('accepts empty array content', function () {
        $renderer = mason([], [TestBrick::class]);

        expect($renderer->toArray())->toBe([]);
    });

    it('sets bricks', function () {
        $renderer = mason([], [TestBrick::class]);

        expect($renderer->getBricks())->toBe([TestBrick::class]);
    });

    it('renders to HTML', function () {
        $content = [
            ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => ['title' => 'Hello']]],
        ];
        $html = mason($content, [TestBrick::class])->toHtml();

        expect($html)->toContain('Hello');
    });

    it('passes data through to the brick', function () {
        $page = Page::factory()->create(['title' => 'Helper Record']);
        $content = [
            ['type' => 'masonBrick', 'attrs' => ['id' => 'data-brick', 'config' => []]],
        ];

        $html = mason($content, [DataBrick::class], ['record' => $page])->toHtml();

        expect($html)->toContain('Helper Record');
    });

    it('defaults data to an empty array', function () {
        expect(mason([], [DataBrick::class])->getData())->toBe([]);
    });

    it('renders to text', function () {
        $content = [
            ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => ['title' => 'Hello']]],
        ];
        $text = mason($content, [TestBrick::class])->toText();

        expect($text)->toContain('Hello')
            ->and($text)->not->toContain('<');
    });

    it('falls back to the default brick list when none is given', function () {
        expect(mason([])->getBricks())->toBe([Section::class]);
    });

    it('accepts content with wrapper', function () {
        $content = [
            'content' => [
                ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => []]],
            ],
        ];
        $renderer = mason($content, [TestBrick::class]);

        expect($renderer)->toBeInstanceOf(MasonRenderer::class)
            ->and($renderer->toArray())->toHaveCount(1);
    });
});

describe('@mason directive', function () {
    $doc = fn (string $id) => [
        'type' => 'doc',
        'content' => [
            ['type' => 'masonBrick', 'attrs' => ['id' => $id, 'config' => ['title' => 'Hello']]],
        ],
    ];

    it('renders a brick from the list passed as a second argument', function () use ($doc) {
        $html = Blade::render(
            '@mason($content, $bricks)',
            ['content' => $doc('test-brick'), 'bricks' => [TestBrick::class]],
        );

        expect($html)->toContain('Hello');
    });

    it('passes data given as a third argument', function () {
        $page = Page::factory()->create(['title' => 'Directive Record']);

        $html = Blade::render(
            '@mason($content, $bricks, $data)',
            [
                'content' => ['type' => 'doc', 'content' => [
                    ['type' => 'masonBrick', 'attrs' => ['id' => 'data-brick', 'config' => []]],
                ]],
                'bricks' => [DataBrick::class],
                'data' => ['record' => $page],
            ],
        );

        expect($html)->toContain('Directive Record');
    });

    it('still renders the default brick list when given content alone', function () {
        $html = Blade::render(
            '@mason($content)',
            ['content' => ['type' => 'doc', 'content' => [
                ['type' => 'masonBrick', 'attrs' => ['id' => 'section', 'config' => []]],
            ]]],
        );

        expect($html)->not->toBeEmpty();
    });
});
