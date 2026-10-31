<?php

declare(strict_types=1);

use Awcodes\Mason\Mason;
use Awcodes\Mason\Support\BrickCommand;
use Awcodes\Mason\Tests\Fixtures\LivewireCommandsForm;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

function commandsForm(array $properties = [])
{
    return Livewire::test(LivewireCommandsForm::class, $properties);
}

function brickIds(array $state): array
{
    return array_map(fn (array $block): string => $block['attrs']['id'], $state);
}

it('allows every command by default', function () {
    $field = Mason::make('content');

    expect($field->isAddable())->toBeTrue()
        ->and($field->isReorderable())->toBeTrue()
        ->and($field->isDeletable())->toBeTrue();
});

describe('on the server', function () {
    it('refuses a turned-off command and runs the others', function (string $property, BrickCommand $refused, array $expected) {
        $field = commandsForm([$property => false])->instance()->getContentField();

        $state = $field->executeCommands([
            $refused,
            BrickCommand::updateBrick(0, ['type' => 'masonBrick', 'attrs' => ['id' => 'simple-brick', 'config' => ['edited' => true]]]),
        ]);

        expect(brickIds($state))->toBe($expected)
            ->and($state[0]['attrs']['config'])->toBe(['edited' => true]);
    })->with([
        'add' => ['isAddable', BrickCommand::insertBrick(['type' => 'masonBrick', 'attrs' => ['id' => 'simple-brick', 'config' => []]], 2), ['simple-brick', 'test-brick']],
        'move' => ['isReorderable', BrickCommand::moveBrick(1, 0), ['simple-brick', 'test-brick']],
        'delete' => ['isDeletable', BrickCommand::deleteBrick(1), ['simple-brick', 'test-brick']],
    ]);

    it('does not open the brick form to add a brick, but to edit one', function () {
        $insert = TestAction::make('handleBrick')->schemaComponent('content')
            ->arguments(['id' => 'test-brick', 'mode' => 'insert']);
        $edit = TestAction::make('handleBrick')->schemaComponent('content')
            ->arguments(['id' => 'test-brick', 'mode' => 'edit', 'blockIndex' => 1, 'config' => ['title' => 'Second']]);

        commandsForm(['isAddable' => false])
            ->mountAction($insert)
            ->assertActionNotMounted($insert)
            ->mountAction($edit)
            ->assertActionMounted($edit);
    });
});

describe('in the editor', function () {
    it('passes the commands to the editor', function () {
        commandsForm(['isAddable' => false, 'isReorderable' => false, 'isDeletable' => false])
            ->assertSeeHtml('addable: false')
            ->assertSeeHtml('reorderable: false')
            ->assertSeeHtml('deletable: false');
    });

    it('offers no bricks to add', function () {
        commandsForm()
            ->assertSeeHtml('class="mason-actions"')
            ->assertSeeHtml('mason-brick-picker-modal');

        commandsForm(['isAddable' => false])
            ->assertDontSeeHtml('class="mason-actions"')
            ->assertDontSeeHtml('mason-brick-picker-modal')
            ->assertSeeHtml('mason-sidebar');
    });

    it('shows the outline without tabs when no bricks can be added', function () {
        commandsForm(['isAddable' => false, 'hasOutline' => true])
            ->assertDontSeeHtml('mason-sidebar-tabs')
            ->assertSeeHtml('class="mason-sidebar-outline"');
    });

    it('offers no clear all without deletable()', function () {
        commandsForm()->assertSeeHtml('clearAllBlocks()');

        commandsForm(['isDeletable' => false])->assertDontSeeHtml('clearAllBlocks()');
    });
});
