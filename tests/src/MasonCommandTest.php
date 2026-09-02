<?php

declare(strict_types=1);

use Awcodes\Mason\Tests\Fixtures\LivewireForm;
use Livewire\Livewire;
use Workbench\App\Models\Page;

describe('Mason command execution via Livewire', function () {
    function createBrick(string $id, array $config = []): array
    {
        return [
            'type' => 'masonBrick',
            'attrs' => [
                'id' => $id,
                'config' => $config,
            ],
        ];
    }

    it('saves content with inserted bricks', function () {
        $brick1 = createBrick('section', ['text' => 'First section']);
        $brick2 = createBrick('section', ['text' => 'Second section']);

        $page = Page::factory()->make();

        Livewire::test(LivewireForm::class)
            ->fillForm([
                'title' => $page->title,
                'content' => [$brick1, $brick2],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $record = Page::query()->first();

        expect($record->content)->toHaveCount(2);
    });

    it('saves content after update', function () {
        $originalBrick = createBrick('section', ['text' => 'Original']);
        $page = Page::factory()->create([
            'content' => [$originalBrick],
        ]);

        $updatedBrick = createBrick('section', ['text' => 'Updated']);

        Livewire::test(LivewireForm::class, ['record' => $page])
            ->fillForm([
                'title' => $page->title,
                'content' => [$updatedBrick],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $record = Page::query()->first();

        expect($record->content)->toHaveCount(1)
            ->and($record->content[0]['attrs']['config']['text'])->toBe('Updated');
    });

    it('saves empty content', function () {
        $page = Page::factory()->make();

        Livewire::test(LivewireForm::class)
            ->fillForm([
                'title' => $page->title,
                'content' => [],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $record = Page::query()->first();

        expect($record->content)->toBeNull();
    });

    it('preserves brick order', function () {
        $brick1 = createBrick('section', ['text' => 'First']);
        $brick2 = createBrick('section', ['text' => 'Second']);
        $brick3 = createBrick('section', ['text' => 'Third']);

        $page = Page::factory()->make();

        Livewire::test(LivewireForm::class)
            ->fillForm([
                'title' => $page->title,
                'content' => [$brick1, $brick2, $brick3],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $record = Page::query()->first();

        expect($record->content)->toHaveCount(3)
            ->and($record->content[0]['attrs']['config']['text'])->toBe('First')
            ->and($record->content[1]['attrs']['config']['text'])->toBe('Second')
            ->and($record->content[2]['attrs']['config']['text'])->toBe('Third');
    });

    it('strips preview and label attributes on save', function () {
        $brickWithPreview = [
            'type' => 'masonBrick',
            'attrs' => [
                'id' => 'section',
                'config' => ['text' => 'Content'],
                'preview' => 'base64encodedpreview',
                'label' => 'Section Label',
            ],
        ];

        $page = Page::factory()->make();

        Livewire::test(LivewireForm::class)
            ->fillForm([
                'title' => $page->title,
                'content' => [$brickWithPreview],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $record = Page::query()->first();

        expect($record->content[0]['attrs'])->not->toHaveKey('preview')
            ->and($record->content[0]['attrs'])->not->toHaveKey('label')
            ->and($record->content[0]['attrs'])->toHaveKey('config')
            ->and($record->content[0]['attrs'])->toHaveKey('id');
    });
});
