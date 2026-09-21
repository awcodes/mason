<?php

declare(strict_types=1);

namespace Awcodes\Mason\Http\Controllers;

use Awcodes\Mason\Support\DataPayload;
use Awcodes\Mason\Support\IframeEntryRenderer;
use Awcodes\Mason\Support\IframeRenderer;
use Awcodes\Mason\Support\RenderContext;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MasonController
{
    public function preview(Request $request): Response
    {
        return $this->render($request, IframeRenderer::class);
    }

    public function entry(Request $request): Response
    {
        return $this->render($request, IframeEntryRenderer::class);
    }

    /**
     * @param  class-string<IframeRenderer|IframeEntryRenderer>  $rendererClass
     */
    private function render(Request $request, string $rendererClass): Response
    {
        $blocksJson = $request->input('blocks');
        $blocks = is_string($blocksJson) ? json_decode($blocksJson, true) : ($blocksJson ?? []);

        if (! is_array($blocks)) {
            $blocks = [];
        }

        // Bricks and layout come from the signed context the field rendered,
        // never from the request body: both name code that runs here.
        $context = RenderContext::decode($request->input('context'));

        $renderer = $rendererClass::make($blocks)->bricks($context['bricks']);

        // Only the entry carries render data today: the editor preview has no
        // record in scope, so mason.js posts none.
        if ($renderer instanceof IframeEntryRenderer) {
            $renderer->data(DataPayload::decode($request->input('data')));
        }

        return response($renderer->toHtml($context['layout']))
            ->header('Content-Type', 'text/html');
    }
}
