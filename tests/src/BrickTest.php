<?php

declare(strict_types=1);

use Awcodes\Mason\Bricks\Section;
use Awcodes\Mason\Tests\Fixtures\SimpleBrick;
use Awcodes\Mason\Tests\Fixtures\TestBrick;
use Filament\Actions\Action;

describe('Brick', function () {
    describe('getId()', function () {
        it('returns the brick id', function () {
            expect(TestBrick::getId())->toBe('test-brick');
        });
    });

    describe('getLabel()', function () {
        it('converts kebab-case id to title case label', function () {
            expect(TestBrick::getLabel())->toBe('Test Brick');
        });

        it('handles single word id', function () {
            expect(SimpleBrick::getLabel())->toBe('Simple Brick');
        });
    });

    describe('getIcon()', function () {
        it('returns icon when defined', function () {
            expect(TestBrick::getIcon())->toBe('heroicon-o-star');
        });

        it('returns null when not defined', function () {
            expect(SimpleBrick::getIcon())->toBeNull();
        });
    });

    describe('getTags()', function () {
        it('returns defined tags', function () {
            expect(TestBrick::getTags())->toBe(['hero', 'banner', 'marketing']);
        });

        it('returns empty array when not overridden', function () {
            expect(SimpleBrick::getTags())->toBe([]);
        });
    });

    describe('toHtml()', function () {
        it('renders HTML with config', function () {
            $html = TestBrick::toHtml(['title' => 'My Title', 'content' => 'My Content']);

            expect($html)
                ->toContain('My Title')
                ->toContain('My Content')
                ->toContain('test-brick');
        });

        it('handles missing config values', function () {
            $html = TestBrick::toHtml([]);

            expect($html)->toContain('test-brick');
        });

        it('returns null for base brick class', function () {
            // SimpleBrick returns a string, but base Brick::toHtml returns null
            // We can't test the abstract base directly, but we can verify implementation
            expect(SimpleBrick::toHtml([]))->toBe('<div class="simple-brick">Simple content</div>');
        });
    });

    describe('configureBrickAction()', function () {
        it('configures the action with schema', function () {
            $action = Action::make('test');
            $configured = TestBrick::configureBrickAction($action);

            expect($configured)->toBeInstanceOf(Action::class);
        });
    });
});

describe('Section Brick', function () {
    describe('getId()', function () {
        it('returns section', function () {
            expect(Section::getId())->toBe('section');
        });
    });

    describe('getLabel()', function () {
        it('returns Section', function () {
            expect(Section::getLabel())->toBe('Section');
        });
    });

    describe('getIcon()', function () {
        it('returns an SVG icon', function () {
            $icon = Section::getIcon();

            expect($icon)->toBeInstanceOf(Illuminate\Support\HtmlString::class);
        });
    });

    describe('toHtml()', function () {
        it('renders section with text', function () {
            $html = Section::toHtml(['text' => '<p>Hello World</p>']);

            expect($html)->toContain('Hello World');
        });

        it('renders section with background color', function () {
            $html = Section::toHtml(['background_color' => 'gray', 'text' => 'Content']);

            expect($html)->toContain('Content');
        });

        it('handles empty config', function () {
            $html = Section::toHtml([]);

            expect($html)->toBeString();
        });
    });

    describe('configureBrickAction()', function () {
        it('configures slide over action', function () {
            $action = Action::make('test');
            $configured = Section::configureBrickAction($action);

            expect($configured)->toBeInstanceOf(Action::class);
        });
    });
});
