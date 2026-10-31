<?php

declare(strict_types=1);

use Awcodes\Mason\Tests\Fixtures\ConfiguredBrick;
use Awcodes\Mason\Tests\Fixtures\LivewireBrickActionForm;
use Filament\Actions\Action;
use Filament\Actions\Testing\TestAction;
use Filament\Support\Enums\Width;
use Livewire\Livewire;

afterEach(function () {
    LivewireBrickActionForm::$modifyBrickActionUsing = null;
});

function brickFormAction(string $mode, string $id = 'configured-brick'): TestAction
{
    return TestAction::make('handleBrick')
        ->schemaComponent('content')
        ->arguments(match ($mode) {
            'insert' => ['id' => $id, 'mode' => 'insert'],
            'edit' => ['id' => $id, 'mode' => 'edit', 'blockIndex' => 0, 'config' => ['heading' => 'Hello']],
        });
}

function mountedBrickAction(string $mode): Action
{
    $component = Livewire::test(LivewireBrickActionForm::class)
        ->mountAction(brickFormAction($mode))
        ->assertActionMounted(brickFormAction($mode));

    return $component->instance()->getMountedAction();
}

it('keeps the brick\'s own configuration without a callback', function () {
    expect(mountedBrickAction('edit'))
        ->getModalHeading()->toBe('From the brick')
        ->getModalWidth()->toBe(Width::TwoExtraLarge);
});

it('applies the field\'s callback after the brick\'s configuration', function (string $mode) {
    LivewireBrickActionForm::$modifyBrickActionUsing = fn (Action $action): Action => $action
        ->slideOver()
        ->modalHeading('From the field');

    expect(mountedBrickAction($mode))
        ->isModalSlideOver()->toBeTrue()
        ->getModalHeading()->toBe('From the field')
        ->getModalWidth()->toBe(Width::TwoExtraLarge);
})->with(['insert', 'edit']);

it('keeps the action when the callback returns null', function () {
    LivewireBrickActionForm::$modifyBrickActionUsing = function (Action $action): ?Action {
        $action->slideOver();

        return null;
    };

    expect(mountedBrickAction('edit'))
        ->isModalSlideOver()->toBeTrue()
        ->getModalHeading()->toBe('From the brick');
});

it('fails when the callback returns a different action', function () {
    LivewireBrickActionForm::$modifyBrickActionUsing = fn (): Action => Action::make('other')->slideOver();

    mountedBrickAction('edit');
})->throws(LogicException::class, 'brickAction()');

it('passes the brick class and the arguments to the callback', function (string $mode) {
    LivewireBrickActionForm::$modifyBrickActionUsing = fn (Action $action, string $brick, array $arguments): Action => $action
        ->modalHeading("{$brick}:{$arguments['mode']}");

    expect(mountedBrickAction($mode)->getModalHeading())->toBe(ConfiguredBrick::class . ":{$mode}");
})->with(['insert', 'edit']);

it('still saves the brick with a customized action', function () {
    LivewireBrickActionForm::$modifyBrickActionUsing = fn (Action $action): Action => $action->slideOver();

    $component = Livewire::test(LivewireBrickActionForm::class)
        ->mountAction(brickFormAction('edit'))
        ->setActionData(['heading' => 'Changed'])
        ->callMountedAction()
        ->assertHasNoActionErrors();

    expect($component->get('data.content.0.attrs.config.heading'))->toBe('Changed');
});
