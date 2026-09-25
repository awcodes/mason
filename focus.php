<?php

declare(strict_types=1);

use Awcodes\Focus\Enums\Theme;
use Awcodes\Focus\Screenshot;
use Awcodes\Focus\ScreenshotSuite;
use Playwright\Locator\LocatorInterface;
use Playwright\Page\PageInterface;

/*
 * Documentation screenshots for Mason, generated with awcodes/focus from the
 * Workbench (run `composer build` first). Every flow closes without saving so
 * the seeded pages keep their fixed content and timestamps.
 *
 * Steps and focus() only search the top-level document, so anything inside
 * the editor's preview iframe is driven from ready() callbacks.
 */

$preview = fn (PageInterface $page, string $selector): LocatorInterface => $page
    ->frameLocator('iframe.mason-iframe')
    ->locator($selector)
    ->first();

$visible = function (LocatorInterface $locator): LocatorInterface {
    // playwright-php polls actionability with a fixed 30s timeout; waiting first fails fast.
    $locator->waitFor(['state' => 'visible']);

    return $locator;
};

$selectFirstBlock = function (PageInterface $page) use ($preview, $visible): void {
    $visible($preview($page, '.mason-block'))->click();
};

$clickInSelectedBlock = fn (string $selector): Closure => function (PageInterface $page) use ($preview, $visible, $selectFirstBlock, $selector): void {
    $selectFirstBlock($page);
    $visible($preview($page, ".mason-block.selected {$selector}"))->click();
};

// Clicks leave the pointer and keyboard focus behind, which shows tooltips and focus rings.
$clearPointerAndFocus = function (PageInterface $page): void {
    $page->mouse()->move(0, 0);
    $page->evaluate('() => document.activeElement?.blur()');
};

// focus() cannot reach into an iframe, so overlay an inert top-level element
// on the selected block and frame that instead.
$markSelectedBlock = function (PageInterface $page) use ($preview, $visible): void {
    $visible($preview($page, '.mason-block.selected [data-focus="mason-block-controls"]'));
    $box = $preview($page, '.mason-block.selected')->boundingBox();

    $page->evaluate(<<<'JS'
        (box) => {
            const marker = document.createElement('div')
            marker.dataset.focus = 'mason-selected-block-frame'
            Object.assign(marker.style, {
                position: 'absolute',
                left: `${box.x + window.scrollX}px`,
                top: `${box.y + window.scrollY}px`,
                width: `${box.width}px`,
                height: `${box.height}px`,
                pointerEvents: 'none',
            })
            document.body.appendChild(marker)
        }
        JS, $box);
};

return ScreenshotSuite::make()
    ->screenshots([
        Screenshot::make('editor')
            ->visit('/admin/pages/1/edit')
            ->focus('[data-focus="mason-editor"]')
            ->padding(8),

        Screenshot::make('brick-sidebar')
            ->visit('/admin/pages/1/edit')
            ->focus('[data-focus="mason-sidebar"]'),

        Screenshot::make('brick-picker')
            ->visit('/admin/pages/1/edit')
            ->ready($clickInSelectedBlock('[data-focus-action="mason-add-brick"]'))
            ->waitFor('[data-focus="mason-brick-picker"]')
            ->beforeCapture($clearPointerAndFocus)
            ->focus('[data-focus="mason-brick-picker"]'),

        Screenshot::make('block-controls')
            ->visit('/admin/pages/1/edit')
            ->ready($selectFirstBlock)
            ->beforeCapture($clearPointerAndFocus)
            ->beforeCapture($markSelectedBlock)
            ->focus('[data-focus="mason-selected-block-frame"]')
            ->padding(0),

        Screenshot::make('mobile-preview')
            ->visit('/admin/pages/1/edit')
            ->click('[data-focus="mason-sidebar"] [data-focus-action="mason-mobile"]')
            ->beforeCapture($clearPointerAndFocus)
            ->focus('[data-focus="mason-editor"]')
            ->padding(8),

        Screenshot::make('edit-brick')
            ->visit('/admin/pages/1/edit')
            ->ready($clickInSelectedBlock('[data-action="edit"]'))
            ->waitFor('.fi-modal-window:visible')
            ->beforeCapture($clearPointerAndFocus)
            ->focus('.fi-modal-window:visible')
            ->padding(0),

        Screenshot::make('entry')
            ->visit('/admin/pages/1')
            ->focus('[data-focus="mason-entry"]')
            ->padding(8),

        Screenshot::make('rendered-page')
            ->visit('/pages/home')
            ->fullPage()
            // The Workbench front end has no dark mode, so a dark capture would be a duplicate.
            ->themes([Theme::Light]),
    ]);
