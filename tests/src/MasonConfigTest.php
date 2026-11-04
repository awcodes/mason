<?php

declare(strict_types=1);

use Awcodes\Mason\Actions\BrickAction;
use Awcodes\Mason\Actions\DuplicateBrickAction;
use Awcodes\Mason\Mason;
use Filament\Actions\Action;

describe('Mason field configuration', function () {
    describe('doubleClickToEdit()', function () {
        it('enables double click to edit', function () {
            $field = Mason::make('content')
                ->doubleClickToEdit();

            expect($field->shouldDblClickToEdit())->toBeTrue();
        });

        it('disables double click to edit', function () {
            $field = Mason::make('content')
                ->doubleClickToEdit(false);

            expect($field->shouldDblClickToEdit())->toBeFalse();
        });

        it('accepts closure', function () {
            $field = Mason::make('content')
                ->doubleClickToEdit(fn () => true);

            expect($field->shouldDblClickToEdit())->toBeTrue();
        });

        it('defaults to false', function () {
            $field = Mason::make('content');

            expect($field->shouldDblClickToEdit())->toBeFalse();
        });
    });

    describe('previewLayout()', function () {
        it('sets preview layout', function () {
            $field = Mason::make('content')
                ->previewLayout('custom.layout');

            expect($field->getPreviewLayout())->toBe('custom.layout');
        });

        it('accepts closure', function () {
            $field = Mason::make('content')
                ->previewLayout(fn () => 'dynamic.layout');

            expect($field->getPreviewLayout())->toBe('dynamic.layout');
        });

        it('falls back to config value when set to null', function () {
            $field = Mason::make('content')
                ->previewLayout(null);

            // Will fall back to config('mason.preview.layout') which has a default
            $layout = $field->getPreviewLayout();

            expect($layout)->toBeString();
        });
    });

    describe('getDefaultActions()', function () {
        it('returns the brick and duplicate actions', function () {
            $field = Mason::make('content');
            $actions = $field->getDefaultActions();

            expect($actions)->toHaveCount(2)
                ->and($actions[0])->toBeInstanceOf(Action::class)
                ->and($actions[0]->getName())->toBe(BrickAction::NAME)
                ->and($actions[1]->getName())->toBe(DuplicateBrickAction::NAME);
        });

        it('keeps the brick form open when clicking outside it', function () {
            expect(BrickAction::make()->isModalClosedByClickingAway())->toBeFalse();
        });

        it('lets a brick close its form by clicking outside it', function () {
            $action = BrickAction::make()->closeModalByClickingAway();

            expect($action->isModalClosedByClickingAway())->toBeTrue();
        });
    });
});
