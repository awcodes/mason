<?php

declare(strict_types=1);

use Awcodes\Mason\Actions\DuplicateBrickAction;
use Awcodes\Mason\Brick;
use Awcodes\Mason\Tests\Fixtures\LivewireDuplicateForm;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

function duplicateBrickAction(int $index): TestAction
{
    return TestAction::make(DuplicateBrickAction::NAME)
        ->schemaComponent('content')
        ->arguments(['index' => $index]);
}

function brickWithConfig(string $id, array $config): array
{
    return [
        'type' => 'masonBrick',
        'attrs' => ['id' => $id, 'config' => $config],
    ];
}

it('inserts a copy right after the brick', function () {
    $component = Livewire::test(LivewireDuplicateForm::class, [
        'initialContent' => [
            brickWithConfig('section', ['text' => '<p>First</p>']),
            brickWithConfig('section', ['text' => '<p>Second</p>']),
        ],
    ])->callAction(duplicateBrickAction(0));

    expect(data_get($component->get('data'), 'content.*.attrs.config.text'))
        ->toBe(['<p>First</p>', '<p>First</p>', '<p>Second</p>'])
        ->and($component->get('data.content.1.attrs.outlineLabel'))->toBe('First')
        ->and($component->get('data.content.1.attrs.preview'))->not->toBeEmpty();
});

it('passes the copy through the brick\'s duplicateConfig()', function () {
    $component = Livewire::test(LivewireDuplicateForm::class, [
        'initialContent' => [brickWithConfig('anchor-brick', ['anchor' => 'intro', 'title' => 'Intro'])],
    ])->callAction(duplicateBrickAction(0));

    expect($component->get('data.content.0.attrs.config'))->toBe(['anchor' => 'intro', 'title' => 'Intro'])
        ->and($component->get('data.content.1.attrs.config'))->toBe(['anchor' => null, 'title' => 'Intro'])
        ->and(base64_decode($component->get('data.content.1.attrs.preview')))->toBe('<section id="">Intro</section>');
});

it('copies the config unchanged by default', function () {
    $brick = new class extends Brick
    {
        public static function getId(): string
        {
            return 'plain';
        }
    };

    expect($brick::duplicateConfig(['anchor' => 'intro']))->toBe(['anchor' => 'intro']);
});

it('does not copy an unregistered brick', function () {
    $component = Livewire::test(LivewireDuplicateForm::class, [
        'initialContent' => [brickWithConfig('missing', ['text' => 'Gone'])],
    ])->callAction(duplicateBrickAction(0));

    expect($component->get('data.content'))->toHaveCount(1);
});

it('ignores an index outside the content', function () {
    $component = Livewire::test(LivewireDuplicateForm::class, [
        'initialContent' => [brickWithConfig('section', ['text' => '<p>Only</p>'])],
    ])->callAction(duplicateBrickAction(5));

    expect($component->get('data.content'))->toHaveCount(1);
});

it('does not copy in a disabled editor', function () {
    $component = Livewire::test(LivewireDuplicateForm::class, [
        'isDisabled' => true,
        'initialContent' => [brickWithConfig('section', ['text' => '<p>Only</p>'])],
    ])->callAction(duplicateBrickAction(0));

    expect($component->get('data.content'))->toHaveCount(1);
});
