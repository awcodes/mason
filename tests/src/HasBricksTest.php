<?php

declare(strict_types=1);

use Awcodes\Mason\BrickGroup;
use Awcodes\Mason\Bricks\Section;
use Awcodes\Mason\Mason;
use Awcodes\Mason\Support\MasonRenderer;
use Awcodes\Mason\Tests\Fixtures\SimpleBrick;
use Awcodes\Mason\Tests\Fixtures\TestBrick;

describe('HasBricks trait', function () {
    describe('on Mason field', function () {
        it('sets bricks via bricks()', function () {
            $field = Mason::make('content')
                ->bricks([TestBrick::class, SimpleBrick::class]);

            expect($field->getBricks())->toBe([TestBrick::class, SimpleBrick::class]);
        });

        it('returns default Section brick when not set', function () {
            $field = Mason::make('content');

            expect($field->getBricks())->toBe([Section::class]);
        });

        it('accepts closure for bricks', function () {
            $field = Mason::make('content')
                ->bricks(fn () => [TestBrick::class]);

            expect($field->getBricks())->toBe([TestBrick::class]);
        });

        it('caches bricks by id', function () {
            $field = Mason::make('content')
                ->bricks([TestBrick::class, SimpleBrick::class]);

            $cached = $field->getCachedBricks();

            expect($cached)->toHaveKey('test-brick')
                ->and($cached)->toHaveKey('simple-brick')
                ->and($cached['test-brick'])->toBe(TestBrick::class)
                ->and($cached['simple-brick'])->toBe(SimpleBrick::class);
        });

        it('returns cached bricks on subsequent calls', function () {
            $field = Mason::make('content')
                ->bricks([TestBrick::class]);

            $first = $field->getCachedBricks();
            $second = $field->getCachedBricks();

            expect($first)->toBe($second);
        });

        it('gets brick by id', function () {
            $field = Mason::make('content')
                ->bricks([TestBrick::class, SimpleBrick::class]);

            expect($field->getBrick('test-brick'))->toBe(TestBrick::class)
                ->and($field->getBrick('simple-brick'))->toBe(SimpleBrick::class);
        });

        it('returns null for unknown brick id', function () {
            $field = Mason::make('content')
                ->bricks([TestBrick::class]);

            expect($field->getBrick('unknown-brick'))->toBeNull();
        });

        it('sorts bricks ascending by label', function () {
            $field = Mason::make('content')
                ->bricks([TestBrick::class, SimpleBrick::class])
                ->sortBricks('asc');

            $cached = $field->getCachedBricks();
            $keys = array_keys($cached);

            expect($keys[0])->toBe('simple-brick')
                ->and($keys[1])->toBe('test-brick');
        });

        it('sorts bricks descending by label', function () {
            $field = Mason::make('content')
                ->bricks([SimpleBrick::class, TestBrick::class])
                ->sortBricks('desc');

            $cached = $field->getCachedBricks();
            $keys = array_keys($cached);

            expect($keys[0])->toBe('test-brick')
                ->and($keys[1])->toBe('simple-brick');
        });

        it('maintains original order when sorting is not set', function () {
            $field = Mason::make('content')
                ->bricks([TestBrick::class, SimpleBrick::class]);

            $cached = $field->getCachedBricks();
            $keys = array_keys($cached);

            expect($keys[0])->toBe('test-brick')
                ->and($keys[1])->toBe('simple-brick');
        });

        it('defaults to ascending when sortBricks is called without argument', function () {
            $field = Mason::make('content')
                ->bricks([TestBrick::class, SimpleBrick::class])
                ->sortBricks();

            expect($field->getBricksSortDirection())->toBe('asc');
        });

        it('accepts BrickGroup instances alongside brick classes', function () {
            $group = BrickGroup::make('Content')->bricks([Section::class]);

            $field = Mason::make('content')
                ->bricks([$group, TestBrick::class]);

            $bricks = $field->getBricks();

            expect($bricks[0])->toBeInstanceOf(BrickGroup::class)
                ->and($bricks[1])->toBe(TestBrick::class);
        });

        it('caches bricks from groups by id', function () {
            $group = BrickGroup::make('Content')->bricks([Section::class, TestBrick::class]);

            $field = Mason::make('content')
                ->bricks([$group, SimpleBrick::class]);

            $cached = $field->getCachedBricks();

            expect($cached)->toHaveKey('section')
                ->and($cached)->toHaveKey('test-brick')
                ->and($cached)->toHaveKey('simple-brick');
        });

        it('retrieves brick by id when brick is inside a group', function () {
            $group = BrickGroup::make('Content')->bricks([Section::class]);

            $field = Mason::make('content')
                ->bricks([$group]);

            expect($field->getBrick('section'))->toBe(Section::class);
        });

        it('returns flat bricks without groups', function () {
            $group = BrickGroup::make('Content')->bricks([Section::class, TestBrick::class]);

            $field = Mason::make('content')
                ->bricks([$group, SimpleBrick::class]);

            expect($field->getFlatBricks())->toBe([Section::class, TestBrick::class, SimpleBrick::class]);
        });

        it('sorts groups alongside standalone bricks by label', function () {
            $group = BrickGroup::make('Alpha')->bricks([Section::class]);

            $field = Mason::make('content')
                ->bricks([$group, TestBrick::class])
                ->sortBricks('asc');

            $bricks = $field->getBricks();

            expect($bricks[0])->toBeInstanceOf(BrickGroup::class)
                ->and($bricks[0]->getLabel())->toBe('Alpha')
                ->and($bricks[1])->toBe(TestBrick::class);
        });
    });

    describe('on MasonRenderer', function () {
        it('sets bricks via bricks()', function () {
            $renderer = MasonRenderer::make([])
                ->bricks([TestBrick::class]);

            expect($renderer->getBricks())->toBe([TestBrick::class]);
        });

        it('returns default Section brick when not set', function () {
            $renderer = MasonRenderer::make([]);

            expect($renderer->getBricks())->toBe([Section::class]);
        });

        it('gets brick by id', function () {
            $renderer = MasonRenderer::make([])
                ->bricks([TestBrick::class, SimpleBrick::class]);

            expect($renderer->getBrick('test-brick'))->toBe(TestBrick::class);
        });

        it('gets a brick registered inside a BrickGroup', function () {
            $renderer = MasonRenderer::make([])
                ->bricks([BrickGroup::make('Content')->bricks([TestBrick::class])]);

            expect($renderer->getBrick('test-brick'))->toBe(TestBrick::class);
        });

        it('returns null for an unknown brick rather than erroring on an empty list', function () {
            $renderer = MasonRenderer::make([])->bricks([]);

            expect($renderer->getBrick('test-brick'))->toBeNull();
        });

        it('caches an empty brick list instead of leaving the property uninitialised', function () {
            $renderer = MasonRenderer::make([])->bricks([]);

            expect($renderer->getCachedBricks())->toBe([])
                ->and($renderer->getCachedBricks())->toBe([]);
        });
    });

    describe('rendering', function () {
        $doc = fn (string $id) => [
            'type' => 'doc',
            'content' => [
                ['type' => 'masonBrick', 'attrs' => ['id' => $id, 'config' => []]],
            ],
        ];

        it('renders a brick registered as a bare class', function () use ($doc) {
            $html = MasonRenderer::make($doc('test-brick'))
                ->bricks([TestBrick::class])
                ->toUnsafeHtml();

            expect($html)->toContain('test-brick');
        });

        it('renders a brick registered inside a BrickGroup', function () use ($doc) {
            $html = MasonRenderer::make($doc('test-brick'))
                ->bricks([BrickGroup::make('Content')->bricks([TestBrick::class])])
                ->toUnsafeHtml();

            expect($html)->toContain('test-brick');
        });

        it('renders nothing for a brick that is not registered', function () use ($doc) {
            $html = MasonRenderer::make($doc('test-brick'))
                ->bricks([SimpleBrick::class])
                ->toUnsafeHtml();

            expect($html)->toBe('');
        });
    });
});
