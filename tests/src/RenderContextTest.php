<?php

declare(strict_types=1);

use Awcodes\Mason\Mason;
use Awcodes\Mason\MasonEntry;
use Awcodes\Mason\Support\IframeEntryRenderer;
use Awcodes\Mason\Support\IframeRenderer;
use Awcodes\Mason\Support\RenderContext;
use Awcodes\Mason\Tests\Fixtures\SimpleBrick;
use Awcodes\Mason\Tests\Fixtures\TestBrick;
use Workbench\App\Models\Page;
use Workbench\App\Models\User;

describe('RenderContext', function () {
    it('round trips bricks and layout', function () {
        $context = RenderContext::decode(RenderContext::encode([TestBrick::class, SimpleBrick::class], 'fixtures.layout'));

        expect($context)->toBe([
            'bricks' => [TestBrick::class, SimpleBrick::class],
            'layout' => 'fixtures.layout',
        ]);
    });

    it('is deterministic, so re-renders do not change the component', function () {
        expect(RenderContext::encode([TestBrick::class], 'fixtures.layout'))
            ->toBe(RenderContext::encode([TestBrick::class], 'fixtures.layout'));
    });

    it('rejects a context whose payload was changed', function () {
        [, $signature] = explode('.', RenderContext::encode([TestBrick::class], null));
        $forged = base64_encode(json_encode(['bricks' => [SimpleBrick::class], 'layout' => 'fixtures.layout'])) . '.' . $signature;

        expect(RenderContext::decode($forged))->toBe(['bricks' => [], 'layout' => null]);
    });

    it('treats a missing or forged context as empty', function (mixed $payload) {
        expect(RenderContext::decode($payload))->toBe(['bricks' => [], 'layout' => null]);
    })->with([
        'null' => [null],
        'empty string' => [''],
        'array' => [[TestBrick::class]],
        'unsigned json' => [json_encode(['bricks' => [TestBrick::class], 'layout' => 'fixtures.layout'])],
        'bad signature' => [base64_encode(json_encode(['bricks' => [TestBrick::class], 'layout' => null])) . '.nope'],
        'not base64' => ['!!!.abc'],
    ]);

    it('drops anything that is not a brick class', function () {
        $context = RenderContext::decode(RenderContext::encode([TestBrick::class, Page::class, 'Nope\\Missing', ['x']], null));

        expect($context['bricks'])->toBe([TestBrick::class]);
    });

    it('is built from the field and entry configuration', function () {
        $field = Mason::make('content')->bricks([TestBrick::class])->previewLayout('fixtures.layout');
        $entry = MasonEntry::make('content')->bricks([SimpleBrick::class]);

        expect(RenderContext::decode($field->getRenderContext()))->toBe([
            'bricks' => [TestBrick::class],
            'layout' => 'fixtures.layout',
        ])->and(RenderContext::decode($entry->getRenderContext()))->toBe([
            'bricks' => [SimpleBrick::class],
            'layout' => config('mason.entry.layout'),
        ]);
    });
});

describe('iframe routes', function () {
    beforeEach(function () {
        $this->actingAs(new User);
    });

    $blocks = json_encode([
        ['type' => 'masonBrick', 'attrs' => ['id' => 'test-brick', 'config' => ['title' => 'Signed Brick']]],
    ]);

    it('renders with the bricks and layout from the signed context', function (string $route) use ($blocks) {
        $response = $this->post(route($route), [
            'blocks' => $blocks,
            'context' => RenderContext::encode([TestBrick::class], 'fixtures.layout'),
        ]);

        $response->assertOk();
        expect($response->getContent())
            ->toContain('data-fixture-layout')
            ->toContain('<div class="test-brick">');
    })->with(['mason.preview', 'mason.entry']);

    it('ignores bricks and a layout posted in the clear', function (string $route) use ($blocks) {
        $response = $this->post(route($route), [
            'blocks' => $blocks,
            'bricks' => json_encode([TestBrick::class]),
            'layout' => 'fixtures.layout',
        ]);

        $response->assertOk();
        expect($response->getContent())
            ->not->toContain('data-fixture-layout')
            ->not->toContain('<div class="test-brick">');
    })->with(['mason.preview', 'mason.entry']);
});

describe('renderer layout config fallback', function () {
    it('reads the configured default layout', function (string $renderer, string $key) {
        config()->set($key, 'fixtures.layout');

        expect($renderer::make([])->toHtml())->toContain('data-fixture-layout');
    })->with([
        'preview' => [IframeRenderer::class, 'mason.preview.layout'],
        'entry' => [IframeEntryRenderer::class, 'mason.entry.layout'],
    ]);
});
