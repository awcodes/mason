<?php

use Awcodes\Mason\Support\Converter;

if (! function_exists(function: 'mason')) {
    function mason(string | array | stdClass | null $content, ?array $bricks = []): Converter
    {
        return (new Converter($content))->bricks($bricks);
    }
}
