<?php

declare(strict_types=1);

use Awcodes\Mason\Mason;
use Awcodes\Mason\Support\BrickCommand;
use Awcodes\Mason\Tests\Fixtures\LimitedBrick;
use Awcodes\Mason\Tests\Fixtures\LivewireMaxInstancesForm;
use Awcodes\Mason\Tests\Fixtures\SimpleBrick;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

function limitedBrick(array $config = []): array
{
    return ['type' => 'masonBrick', 'attrs' => ['id' => 'limited-brick', 'config' => $config]];
}

function simpleBrick(): array
{
    return ['type' => 'masonBrick', 'attrs' => ['id' => 'simple-brick', 'config' => []]];
}

function insertBrickAction(string $id): TestAction
{
    return TestAction::make('handleBrick')
        ->schemaComponent('content')
        ->arguments(['id' => $id, 'mode' => 'insert']);
}

it('puts no limit on a brick by default', function () {
    expect(SimpleBrick::getMaxInstances())->toBeNull();
});

it('passes only the limited bricks to the editor', function () {
    $field = Mason::make('content')->bricks([LimitedBrick::class, SimpleBrick::class]);

    expect($field->getBrickLimits())->toBe(['limited-brick' => 1]);
});

it('inserts a limited brick while it is below its limit', function () {
    $component = Livewire::test(LivewireMaxInstancesForm::class, ['initialContent' => [simpleBrick()]])
        ->mountAction(insertBrickAction('limited-brick'));

    expect(data_get($component->get('data'), 'content.*.attrs.id'))
        ->toBe(['simple-brick', 'limited-brick']);
});

it('does not insert a brick that has reached its limit', function () {
    $component = Livewire::test(LivewireMaxInstancesForm::class, ['initialContent' => [limitedBrick()]])
        ->mountAction(insertBrickAction('limited-brick'))
        ->assertActionNotMounted(insertBrickAction('limited-brick'));

    expect(data_get($component->get('data'), 'content.*.attrs.id'))->toBe(['limited-brick']);
});

it('still inserts other bricks and edits the limited one at its limit', function () {
    $component = Livewire::test(LivewireMaxInstancesForm::class, ['initialContent' => [limitedBrick()]])
        ->mountAction(insertBrickAction('simple-brick'))
        ->mountAction(TestAction::make('handleBrick')
            ->schemaComponent('content')
            ->arguments(['id' => 'limited-brick', 'mode' => 'edit', 'blockIndex' => 0, 'config' => []]));

    expect(data_get($component->get('data'), 'content.*.attrs.id'))->toBe(['limited-brick', 'simple-brick'])
        ->and($component->get('data.content.0.attrs'))->toHaveKey('preview');
});

it('ignores an insert command beyond the limit', function () {
    $livewire = Livewire::test(LivewireMaxInstancesForm::class, ['initialContent' => [limitedBrick()]])->instance();

    $state = $livewire->getContentField()->executeCommands([
        BrickCommand::insertBrick(limitedBrick(), 1),
        BrickCommand::insertBrick(simpleBrick(), 1),
    ]);

    expect(data_get($state, '*.attrs.id'))->toBe(['limited-brick', 'simple-brick']);
});

it('fails validation when the content has more of a brick than its limit', function (string $locale) {
    app()->setLocale($locale);

    Livewire::test(LivewireMaxInstancesForm::class)
        ->fillForm(['title' => 'Home', 'content' => [limitedBrick(), simpleBrick(), limitedBrick()]])
        ->call('create')
        ->assertHasFormErrors(['content' => trans_choice('mason::mason.max_instances.exceeded', 1, [
            'label' => LimitedBrick::getLabel(),
            'max' => 1,
        ])]);
})->with(['en', 'ar']);

it('saves content within the limit', function () {
    Livewire::test(LivewireMaxInstancesForm::class)
        ->fillForm(['title' => 'Home', 'content' => [limitedBrick(), simpleBrick()]])
        ->call('create')
        ->assertHasNoFormErrors();
});

it('greys out a limited brick in the sidebar and the picker once it is used up', function () {
    Livewire::test(LivewireMaxInstancesForm::class)
        ->assertSeeHtml("'unavailable': ! isBrickAvailable(")
        ->assertSeeHtml('x-bind:disabled="! isBrickAvailable(')
        ->assertSeeHtml('Can only be used once');
});

it('shows how often a limited brick is used, in the sidebar and the picker', function () {
    $html = Livewire::test(LivewireMaxInstancesForm::class)->html();

    expect(substr_count($html, 'class="mason-brick-count"'))->toBe(2)
        ->and($html)->toContain("brickCount('limited-brick')}/1")
        ->not->toContain("brickCount('simple-brick')");
});
