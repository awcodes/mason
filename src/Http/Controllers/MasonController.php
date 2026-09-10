<?php

declare(strict_types=1);

namespace Awcodes\Mason\Http\Controllers;

use Awcodes\Mason\Support\DataPayload;
use Awcodes\Mason\Support\IframeEntryRenderer;
use Awcodes\Mason\Support\IframeRenderer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MasonController
{
    public function preview(Request $request): Response
    {
        return $this->render($request, IframeRenderer::class, 'mason.iframe.layout');
    }

    public function entry(Request $request): Response
    {
        return $this->render($request, IframeEntryRenderer::class, 'mason.iframe-entry.layout');
    }

    /**
     * @param  class-string<IframeRenderer|IframeEntryRenderer>  $rendererClass
     */
    private function render(Request $request, string $rendererClass, string $layoutConfigKey): Response
    {
        $blocksJson = $request->input('blocks');
        $bricksJson = $request->input('bricks');
        $layout = $request->input('layout');

        $blocks = is_string($blocksJson) ? json_decode($blocksJson, true) : ($blocksJson ?? []);
        $bricks = is_string($bricksJson) ? json_decode($bricksJson, true) : ($bricksJson ?? []);

        if (! is_array($blocks)) {
            $blocks = [];
        }

        $renderer = $rendererClass::make($blocks);

        if (filled($bricks) && is_array($bricks)) {
            $brickClasses = array_map(function ($brick) {
                if (is_string($brick) && class_exists($brick)) {
                    return $brick;
                }

                return $brick;
            }, $bricks);

            $renderer->bricks($brickClasses);
        }

        // Only the entry carries render data today: the editor preview has no
        // record in scope, so mason.js posts none.
        if ($renderer instanceof IframeEntryRenderer) {
            $renderer->data(DataPayload::decode($request->input('data')));
        }

        $layoutToUse = $layout ?? config($layoutConfigKey);

        return response($renderer->toHtml($layoutToUse))
            ->header('Content-Type', 'text/html');
    }
}
