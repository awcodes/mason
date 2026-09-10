<?php

declare(strict_types=1);

use Awcodes\Mason\Support\MasonRenderer;

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
