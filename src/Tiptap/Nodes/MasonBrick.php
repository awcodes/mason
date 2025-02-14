<?php

namespace Awcodes\Mason\Tiptap\Nodes;

use Awcodes\Mason\Bricks\Section;
use Tiptap\Core\Node;

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
            'identifier' => [
                'default' => null,
            ],
            'values' => [
                'default' => [],
            ],
            'path' => [
                'default' => null,
            ],
            'view' => [
                'default' => null,
            ],
        ];
    }

    public function parseHTML(): array
    {
        return [
            [
                'tag' => 'mason-brick',
                'getAttrs' => function ($DOMNode) {
                    return json_decode($DOMNode->nodeValue, true);
                },
            ],
        ];
    }

    public function renderHTML($node, $HTMLAttributes = []): array
    {
        $data = $HTMLAttributes;
        $view = null;
        $brickData = json_decode(json_encode($data), true);

        if ($data) {
            foreach ($this->getBricks() as $brick) {
                if ($brick->getName() === $data['identifier']) {
                    $view = view($data['path'], $brickData['values'])->toHtml();
                }
            }
        }

        return [
            'content' => '<div class="mason-brick">' . $view . '</div>',
        ];
    }

    public function getBricks(): array
    {
        $bricks = $this->options['bricks'] ?? null;

        if (blank($bricks)) {
            $bricks = [
                Section::make(),
            ];
        }

        return collect($bricks)
            ->mapWithKeys(function ($brick) {
                return [$brick->getName() => $brick];
            })
            ->all();
    }
}
