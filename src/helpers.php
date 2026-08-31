<?php

declare(strict_types=1);

use Awcodes\Mason\Support\MasonRenderer;

if (! function_exists(function: 'mason')) {
    function mason(string | array | stdClass | null $content, ?array $bricks = null): MasonRenderer
    {
        return MasonRenderer::make($content)->bricks($bricks);
    }
}
