<?php

namespace Awcodes\Mason\Tiptap\Nodes;

use Awcodes\Mason\Bricks\Section;
use Tiptap\Core\Node;
use Tiptap\Utils\HTML;

class MasonBrick extends Node
{
    public static $name = 'masonBrick';

    public function addOptions(): array
    {
        return [
            'bricks' => [],
        ];
    }

    public function addAttributes(): array
    {
        return [
            'config' => [
                'parseHTML' => fn ($DOMNode) => json_decode($DOMNode->getAttribute('data-config')) ?: null,
                'renderHTML' => fn ($attributes) => ['data-config' => json_encode($attributes->config ?? null)],
            ],
            'id' => [
                'parseHTML' => fn ($DOMNode) => $DOMNode->getAttribute('data-id') ?: null,
                'renderHTML' => fn ($attributes) => ['data-id' => $attributes->id ?? null],
            ],
            'label' => [
                'parseHTML' => fn ($DOMNode) => $DOMNode->getAttribute('data-label') ?: null,
                'rendered' => false,
            ],
            'preview' => [
                'parseHTML' => fn ($DOMNode) => base64_decode($DOMNode->getAttribute('data-preview') ?: null),
                'rendered' => false,
            ],
        ];
    }

    public function parseHTML(): array
    {
        return [
            [
                'tag' => 'div[data-type="brick"]',
            ],
        ];
    }

    public function renderHTML($node, $HTMLAttributes = []): array
    {
        return [
            'div',
            HTML::mergeAttributes(
                ['data-type' => self::$name],
                $HTMLAttributes,
            ),
        ];
    }
}
