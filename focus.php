<?php

declare(strict_types=1);

use Awcodes\Focus\Card;
use Awcodes\Focus\Enums\Size;
use Awcodes\Focus\Enums\Theme;
use Awcodes\Focus\Screenshot;
use Awcodes\Focus\ScreenshotSuite;

/*
 * Documentation screenshots for Mason, generated with awcodes/focus from the
 * Workbench. Regenerate with `composer focus` (run `composer build` first). Every flow closes without saving so
 * the seeded pages keep their fixed content and timestamps.
 */

$preview = 'iframe.mason-iframe';
$firstBlock = '.mason-block[data-block-index="0"]';

// The form's Save and Cancel buttons sit just below the editor, inside the default padding.
$formActions = '.fi-sc-actions:has([type="submit"])';

// The awcodes card templates frame each screenshot at 1400x816, so card screenshots are captured at that size.
$cardSlot = [1400, 816];

return ScreenshotSuite::make()
    ->screenshots([
        Screenshot::make('editor')
            ->visit('/admin/pages/1/edit')
            ->hide($formActions)
            ->focus('[data-focus="mason-editor"]'),

        Screenshot::make('brick-sidebar')
            ->visit('/admin/pages/1/edit')
            ->focus('[data-focus="mason-sidebar"]'),

        Screenshot::make('brick-picker')
            ->visit('/admin/pages/1/edit')
            ->within($preview, fn (Screenshot $screenshot): Screenshot => $screenshot
                ->click($firstBlock)
                ->click('.mason-block.selected [data-focus-action="mason-add-brick"]'))
            ->waitFor('[data-focus="mason-brick-picker"]')
            ->focus('[data-focus="mason-brick-picker"]'),

        Screenshot::make('block-controls')
            ->visit('/admin/pages/1/edit')
            ->within($preview, fn (Screenshot $screenshot): Screenshot => $screenshot
                ->click($firstBlock)
                ->focus('.mason-block.selected'))
            ->padding(0),

        Screenshot::make('mobile-preview')
            ->visit('/admin/pages/1/edit')
            ->click('[data-focus="mason-sidebar"] [data-focus-action="mason-mobile"]')
            ->hide($formActions)
            ->focus('[data-focus="mason-editor"]'),

        Screenshot::make('edit-brick')
            ->visit('/admin/pages/1/edit')
            ->within($preview, fn (Screenshot $screenshot): Screenshot => $screenshot
                ->click($firstBlock)
                ->click('.mason-block.selected [data-action="edit"]'))
            ->waitFor('.fi-modal-window:visible')
            ->focus('.fi-modal-window:visible')
            // The slide-over fills the viewport height; padding only adds backdrop.
            ->padding(0),

        Screenshot::make('entry')
            ->visit('/admin/pages/1')
            ->focus('[data-focus="mason-entry"]'),

        Screenshot::make('rendered-page')
            ->visit('/pages/home')
            ->fullPage()
            // The Workbench front end has no dark mode, so a dark capture would be a duplicate.
            ->themes([Theme::Light]),

        // Share-image sources: the editor and the brick picker, shaped to the card templates' screenshot slots.
        // The two-up templates show the editor dark and the brick picker light, so the picker is captured in both.
        // The whole top of the page at the slot's shape, so the heading is never cut; centring on the editor was.
        Screenshot::make('card-editor')
            ->viewportSize(...$cardSlot)
            ->visit('/admin/pages/1/edit')
            ->hide($formActions)
            ->viewport()
            ->themes([Theme::Dark]),

        Screenshot::make('card-brick-picker')
            ->visit('/admin/pages/1/edit')
            ->within($preview, fn (Screenshot $screenshot): Screenshot => $screenshot
                ->click($firstBlock)
                ->click('.mason-block.selected [data-focus-action="mason-add-brick"]'))
            ->waitFor('[data-focus="mason-brick-picker"]')
            ->focus('[data-focus="mason-brick-picker"]')
            ->minSize(...$cardSlot),
    ])
    ->cardTemplates('https://github.com/awcodes/focus-templates/tree/v1.1.1/dist')
    ->cards([
        // Open Graph and the GitHub social preview share one 2400x1260 template; GitHub crops 30px top and bottom.
        Card::make('social')
            ->template('two-up-wide')
            ->screenshots(['card-editor', 'card-brick-picker'])
            ->sizes([Size::OpenGraph, Size::GitHubSocial]),

        // The Filament plugin directory's 2560x1440 thumbnail. A YouTube card would be the same 16:9 canvas and
        // template, so it would only ever be a copy of this one.
        Card::make('thumbnail')
            ->template('two-up')
            ->screenshots(['card-editor', 'card-brick-picker'])
            ->sizes([Size::Filament]),
    ]);
