<?php

declare(strict_types=1);

use Awcodes\Mason\Mason;
use Awcodes\Mason\Support\BrickCommand;
use Awcodes\Mason\Support\BrickTree;
use Awcodes\Mason\Support\Faker;
use Awcodes\Mason\Support\IframeEntryRenderer;
use Awcodes\Mason\Support\IframeRenderer;
use Awcodes\Mason\Support\MasonRenderer;
use Awcodes\Mason\Tests\Fixtures\ContainerBrick;
use Awcodes\Mason\Tests\Fixtures\LivewireNestedForm;
use Awcodes\Mason\Tests\Fixtures\RestrictedContainerBrick;
use Awcodes\Mason\Tests\Fixtures\SimpleBrick;
use Awcodes\Mason\Tests\Fixtures\TestBrick;
use Livewire\Livewire;
use Workbench\App\Models\Page;

function nestedBrick(string $id, array $config = [], ?array $content = null, array $attrs = []): array
{
    $brick = [
        'type' => 'masonBrick',
        'attrs' => ['id' => $id, 'config' => $config, ...$attrs],
    ];

    if ($content !== null) {
        $brick['content'] = $content;
    }

    return $brick;
}

function nestedBricks(): array
{
    return [SimpleBrick::class, TestBrick::class, ContainerBrick::class, RestrictedContainerBrick::class];
}

function nestedMason(array $state): Mason
{
    $livewire = Livewire::test(LivewireNestedForm::class)
        ->fillForm(['content' => $state])
        ->instance();

    return $livewire->form->getComponent(fn ($component): bool => $component instanceof Mason);
}

describe('BrickTree', function () {
    $tree = fn (): array => [
        nestedBrick('test-brick', ['title' => 'A']),
        nestedBrick('container-brick', [], [
            nestedBrick('test-brick', ['title' => 'B']),
            nestedBrick('container-brick', [], [
                nestedBrick('test-brick', ['title' => 'C']),
            ]),
        ]),
        nestedBrick('test-brick', ['title' => 'D']),
    ];

    it('gets bricks by path', function () use ($tree) {
        expect(BrickTree::get($tree(), '0')['attrs']['config']['title'])->toBe('A')
            ->and(BrickTree::get($tree(), '1.0')['attrs']['config']['title'])->toBe('B')
            ->and(BrickTree::get($tree(), '1.1.0')['attrs']['config']['title'])->toBe('C')
            ->and(BrickTree::get($tree(), 2)['attrs']['config']['title'])->toBe('D')
            ->and(BrickTree::get($tree(), '1.5'))->toBeNull()
            ->and(BrickTree::get($tree(), ''))->toBeNull();
    });

    it('inserts into a container', function () use ($tree) {
        $blocks = BrickTree::insert($tree(), '1.1', 0, nestedBrick('test-brick', ['title' => 'New']));

        expect(BrickTree::get($blocks, '1.1.0')['attrs']['config']['title'])->toBe('New')
            ->and(BrickTree::get($blocks, '1.1.1')['attrs']['config']['title'])->toBe('C');
    });

    it('inserts into an empty container', function () {
        $blocks = BrickTree::insert([nestedBrick('container-brick')], '0', null, nestedBrick('test-brick'));

        expect($blocks[0]['content'])->toHaveCount(1);
    });

    it('removes nested bricks', function () use ($tree) {
        $blocks = BrickTree::remove($tree(), '1.0');

        expect($blocks[1]['content'])->toHaveCount(1)
            ->and(BrickTree::get($blocks, '1.0.0')['attrs']['config']['title'])->toBe('C');
    });

    it('moves a brick into a container that follows it', function () use ($tree) {
        // Taking "A" out shifts the container from 1 to 0
        $blocks = BrickTree::move($tree(), '0', '1.1', 1);

        expect($blocks)->toHaveCount(2)
            ->and(BrickTree::get($blocks, '0.1.1')['attrs']['config']['title'])->toBe('A');
    });

    it('moves a nested brick to the top level', function () use ($tree) {
        $blocks = BrickTree::move($tree(), '1.1.0', '', 0);

        expect($blocks)->toHaveCount(4)
            ->and($blocks[0]['attrs']['config']['title'])->toBe('C')
            ->and($blocks[2]['content'][1]['content'])->toBe([]);
    });

    it('does not move a container into itself', function () use ($tree) {
        expect(BrickTree::move($tree(), '1', '1.1', 0))->toBe($tree());
    });

    it('walks every brick with its path', function () use ($tree) {
        $paths = [];

        BrickTree::walk($tree(), function (array $block, string $path) use (&$paths): void {
            $paths[] = $path;
        });

        expect($paths)->toBe(['0', '1', '1.0', '1.1', '1.1.0', '2']);
    });
});

describe('container bricks', function () {
    it('renders the bricks inside a container', function () {
        $html = MasonRenderer::make([
            nestedBrick('container-brick', ['title' => 'Outer'], [
                nestedBrick('simple-brick', attrs: ['layout' => ['span' => 2]]),
                nestedBrick('container-brick', ['title' => 'Inner'], [
                    nestedBrick('test-brick', ['title' => 'Deep']),
                ]),
            ]),
        ])->bricks(nestedBricks())->toUnsafeHtml();

        expect($html)
            ->toContain('<div class="container" data-title="Outer"><div class="cell" data-span="2"><div class="simple-brick">')
            ->toContain('data-title="Inner"><div class="cell" data-span="1"><div class="test-brick"><h2>Deep</h2>')
            ->not->toContain('mason-block')
            ->not->toContain('mason-drop-zone');
    });

    it('leaves out bricks the container does not accept', function () {
        $html = MasonRenderer::make([
            nestedBrick('restricted-container-brick', [], [
                nestedBrick('simple-brick'),
                nestedBrick('test-brick', ['title' => 'Not allowed']),
            ]),
        ])->bricks(nestedBricks())->toUnsafeHtml();

        expect($html)->toContain('simple-brick')
            ->not->toContain('Not allowed');
    });

    it('renders nested bricks as text', function () {
        $text = Faker::make()
            ->brick('container-brick', [], Faker::make()->brick('test-brick', ['title' => 'Nested title']))
            ->asJson();

        expect(MasonRenderer::make($text)->bricks(nestedBricks())->toText())->toContain('Nested title');
    });

    it('wraps nested bricks in editor controls in the preview', function () {
        $html = IframeRenderer::make([
            nestedBrick('simple-brick'),
            nestedBrick('container-brick', [], [
                nestedBrick('test-brick', ['title' => 'Nested']),
            ]),
        ])->bricks(nestedBricks())->toHtml();

        expect($html)
            ->toContain('data-block-path="1.0"')
            ->toContain('data-drop-parent="1"')
            ->toContain('data-accepts="simple-brick,test-brick,container-brick,restricted-container-brick"')
            ->toContain('<h2>Nested</h2>');
    });

    it('lists only allowed bricks as accepted', function () {
        $html = IframeRenderer::make([
            nestedBrick('restricted-container-brick'),
        ])->bricks(nestedBricks())->toHtml();

        expect($html)->toContain('data-accepts="simple-brick"')
            ->toContain('mason-drop-zone-empty');
    });

    it('renders nested bricks in the entry', function () {
        $html = IframeEntryRenderer::make([
            nestedBrick('container-brick', [], [nestedBrick('simple-brick')]),
        ])->bricks(nestedBricks())->toHtml();

        expect($html)->toContain('<div class="cell" data-span="1"><div class="simple-brick">')
            ->not->toContain('mason-drop-zone');
    });
});

describe('Mason commands on nested bricks', function () {
    it('inserts into a container', function () {
        $state = nestedMason([nestedBrick('container-brick')])->executeCommands([
            BrickCommand::insertBrick(nestedBrick('simple-brick'), 0, '0'),
        ]);

        expect($state[0]['content'][0]['attrs']['id'])->toBe('simple-brick');
    });

    it('refuses bricks a container does not accept', function () {
        $state = nestedMason([nestedBrick('restricted-container-brick')])->executeCommands([
            BrickCommand::insertBrick(nestedBrick('test-brick'), 0, '0'),
        ]);

        expect($state[0])->not->toHaveKey('content');
    });

    it('refuses to insert into a brick that is not a container', function () {
        $state = nestedMason([nestedBrick('simple-brick')])->executeCommands([
            BrickCommand::insertBrick(nestedBrick('simple-brick'), 0, '0'),
        ]);

        expect($state[0])->not->toHaveKey('content');
    });

    it('keeps the bricks inside a container when it is edited', function () {
        $state = nestedMason([
            nestedBrick('container-brick', ['title' => 'Old'], [nestedBrick('simple-brick')]),
        ])->executeCommands([
            BrickCommand::updateBrick(0, nestedBrick('container-brick', ['title' => 'New'])),
        ]);

        expect($state[0]['attrs']['config']['title'])->toBe('New')
            ->and($state[0]['content'])->toHaveCount(1);
    });

    it('keeps attributes like layout data when a brick is edited', function () {
        $state = nestedMason([
            nestedBrick('container-brick', [], [
                nestedBrick('test-brick', ['title' => 'Old'], attrs: ['layout' => ['lg' => ['col' => 2]]]),
            ]),
        ])->executeCommands([
            BrickCommand::updateBrick('0.0', nestedBrick('test-brick', ['title' => 'New'])),
        ]);

        expect($state[0]['content'][0]['attrs'])
            ->config->toBe(['title' => 'New'])
            ->layout->toBe(['lg' => ['col' => 2]]);
    });

    it('updates and deletes nested bricks by path', function () {
        $mason = nestedMason([
            nestedBrick('container-brick', [], [
                nestedBrick('test-brick', ['title' => 'Old']),
                nestedBrick('simple-brick'),
            ]),
        ]);

        $state = $mason->executeCommands([
            BrickCommand::updateBrick('0.0', nestedBrick('test-brick', ['title' => 'New'])),
            BrickCommand::deleteBrick('0.1'),
        ]);

        expect($state[0]['content'])->toHaveCount(1)
            ->and($state[0]['content'][0]['attrs']['config']['title'])->toBe('New');
    });

    it('moves a brick into a container', function () {
        $state = nestedMason([
            nestedBrick('simple-brick'),
            nestedBrick('container-brick', [], []),
        ])->executeCommands([
            BrickCommand::moveBrick(0, 0, '1'),
        ]);

        expect($state)->toHaveCount(1)
            ->and($state[0]['content'][0]['attrs']['id'])->toBe('simple-brick');
    });

    it('strips editor attributes of nested bricks on save', function () {
        Livewire::test(LivewireNestedForm::class)
            ->fillForm([
                'title' => 'Nested',
                'content' => [
                    nestedBrick('container-brick', [], [
                        nestedBrick('simple-brick', attrs: ['preview' => 'abc', 'label' => 'Simple', 'outlineLabel' => 'x']),
                    ], ['label' => 'Container']),
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $content = Page::query()->first()->content;

        expect($content[0]['attrs'])->not->toHaveKey('label')
            ->and($content[0]['content'][0]['attrs'])->toBe(['id' => 'simple-brick', 'config' => []]);
    });
});
