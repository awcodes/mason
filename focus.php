<?php

declare(strict_types=1);

use Awcodes\Focus\Enums\Theme;
use Awcodes\Focus\Screenshot;
use Awcodes\Focus\ScreenshotSuite;
use Playwright\Page\PageInterface;

/*
 * Documentation screenshots for Mason, generated with awcodes/focus from the
 * Workbench (run `composer build` first). Every flow closes without saving so
 * the seeded pages keep their fixed content and timestamps.
 */

$preview = 'iframe.mason-iframe';
$firstBlock = '.mason-block[data-block-index="0"]';

// The form's Save and Cancel buttons sit just below the editor, inside the default padding.
// Hidden rather than removed, so the layout and the framing do not move.
$hideFormActions = fn (PageInterface $page): PageInterface => $page->addStyleTag([
    'content' => '.fi-sc-actions:has([type="submit"]) { visibility: hidden !important; }',
]);

return ScreenshotSuite::make()
    ->screenshots([
        Screenshot::make('editor')
            ->visit('/admin/pages/1/edit')
            ->beforeCapture($hideFormActions)
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
            ->beforeCapture($hideFormActions)
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
    ]);
