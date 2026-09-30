<?php

declare(strict_types=1);

use Awcodes\Mason\Enums\SidebarPosition;
use Awcodes\Mason\Mason;
use Filament\Actions\Action;

describe('HasSidebar trait', function () {
    describe('sidebar()', function () {
        it('sets sidebar actions', function () {
            $action = Action::make('custom');
            $field = Mason::make('content')
                ->sidebar([$action]);

            expect($field->getSidebarActions())->toHaveCount(1);
        });

        it('accepts closure for sidebar actions', function () {
            $action = Action::make('custom');
            $field = Mason::make('content')
                ->sidebar(fn () => [$action]);

            expect($field->getSidebarActions())->toHaveCount(1);
        });

        it('returns empty array when not set', function () {
            $field = Mason::make('content');

            expect($field->getSidebarActions())->toBe([]);
        });
    });

    describe('sidebarPosition()', function () {
        it('sets position to Start', function () {
            $field = Mason::make('content')
                ->sidebarPosition(SidebarPosition::Start);

            expect($field->getSidebarPosition())->toBe(SidebarPosition::Start);
        });

        it('sets position to End', function () {
            $field = Mason::make('content')
                ->sidebarPosition(SidebarPosition::End);

            expect($field->getSidebarPosition())->toBe(SidebarPosition::End);
        });

        it('accepts closure for position', function () {
            $field = Mason::make('content')
                ->sidebarPosition(fn () => SidebarPosition::Start);

            expect($field->getSidebarPosition())->toBe(SidebarPosition::Start);
        });

        it('defaults to End when not set', function () {
            $field = Mason::make('content');

            expect($field->getSidebarPosition())->toBe(SidebarPosition::End);
        });
    });

    describe('outline()', function () {
        it('enables the outline', function () {
            $field = Mason::make('content')
                ->outline();

            expect($field->hasOutline())->toBeTrue();
        });

        it('accepts a condition', function () {
            $field = Mason::make('content')
                ->outline(false);

            expect($field->hasOutline())->toBeFalse();
        });

        it('accepts closure for condition', function () {
            $field = Mason::make('content')
                ->outline(fn () => true);

            expect($field->hasOutline())->toBeTrue();
        });

        it('defaults to false when not set', function () {
            $field = Mason::make('content');

            expect($field->hasOutline())->toBeFalse();
        });
    });
});

describe('SidebarPosition enum', function () {
    it('has Start case', function () {
        expect(SidebarPosition::Start->value)->toBe('start');
    });

    it('has End case', function () {
        expect(SidebarPosition::End->value)->toBe('end');
    });
});
