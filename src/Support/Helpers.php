<?php

namespace Awcodes\Mason\Support;

use Awcodes\Mason\Mason;
use Throwable;

class Helpers
{
    /**
     * @throws Throwable
     */
    public static function renderBricks(array $document, Mason $component): array
    {
        $content = $document['content'];

        foreach ($content as $k => $brick) {
            if ($brick['type'] === 'masonBrick') {
                $instance = $component->getAction($brick['attrs']['identifier']);
                if ($instance) {
                    $content[$k]['attrs']['view'] = view($brick['attrs']['path'], $brick['attrs']['values'])->render();
                } else {
                    $content[$k]['attrs']['view'] = view('mason::components.unregistered-block', [
                        'identifier' => $brick['attrs']['identifier'],
                    ])->render();
                }
            } elseif (array_key_exists('content', $brick)) {
                $content[$k] = self::renderBricks($brick, $component);
            }
        }

        $document['content'] = $content;

        return $document;
    }

    public static function sanitizeBricks(array $document): array
    {
        $content = $document['content'];

        foreach ($content as $k => $brick) {
            if ($brick['type'] === 'masonBrick') {
                unset($content[$k]['attrs']['view']);
            } elseif (array_key_exists('content', $brick)) {
                $content[$k] = self::sanitizeBricks($brick);
            }
        }

        $document['content'] = $content;

        return $document;
    }
}
