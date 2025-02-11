<?php

use Awcodes\Mason\Support\Converter;

if (! function_exists('mason')) {
    function mason(string | array | stdClass | null $content): Converter
    {
        return new Converter($content);
    }
}
