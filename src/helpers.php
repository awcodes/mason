<?php

declare(strict_types=1);

use Awcodes\Mason\Mason;
use Awcodes\Mason\Support\MasonRenderer;
use Illuminate\Support\HtmlString;

if (! function_exists(function: 'mason')) {
    /**
     * @param  array<class-string<Awcodes\Mason\Brick>|Awcodes\Mason\BrickGroup>|null  $bricks
     * @param  array<string, mixed>  $data
     */
    function mason(string | array | stdClass | null $content, ?array $bricks = null, array $data = []): MasonRenderer
    {
        return MasonRenderer::make($content)
            ->bricks($bricks)
            ->data($data);
    }
}

if (! function_exists(function: 'mason_part')) {
    /**
     * Marks an element of a brick's view as a part: in the editor preview, clicking it on the selected brick opens the
     * brick's form at the component with this key. Outputs nothing outside the preview.
     */
    function mason_part(string $key): HtmlString
    {
        return new HtmlString(Mason::isRenderingPreview() ? 'data-mason-part="' . e($key) . '"' : '');
    }
}
