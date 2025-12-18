<?php

declare(strict_types=1);

use Awcodes\Mason\Tests\Fixtures\LivewireForm;
use Awcodes\Mason\Tests\Models\Page;
use Livewire\Livewire;

it('has editor field', function () {
    Livewire::test(LivewireForm::class)
        ->assertFormFieldExists('content');
});

it('saves correctly', function () {
    $page = Page::factory()->make();

    Livewire::test(LivewireForm::class)
        ->assertFormFieldExists('content')
        ->fillForm([
            'title' => $page->title,
            'content' => $page->content,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Page::class, [
        'title' => $page->title,
    ]);

    $record = Page::query()->first();

    expect($record)
        ->content->toBe($page->content);
});

it('updated correctly', function () {
    $page = Page::factory()->create();
    $newData = Page::factory()->make();

    Livewire::test(LivewireForm::class, [
        'record' => $page,
    ])
        ->assertFormFieldExists('content')
        ->fillForm([
            'title' => $newData->title,
            'content' => $newData->content,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Page::class, [
        'title' => $newData->title,
    ]);

    $record = Page::query()->first();

    expect($record)
        ->content->toBe($newData->content);
});

it('can create null', function () {
    $page = Page::factory()->make();

    Livewire::test(LivewireForm::class)
        ->assertFormFieldExists('content')
        ->fillForm([
            'title' => $page->title,
            'content' => null,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Page::class, [
        'title' => $page->title,
    ]);

    $record = Page::query()->first();

    expect($record)
        ->content->toBeNull();
});
