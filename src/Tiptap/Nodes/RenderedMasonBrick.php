<?php

declare(strict_types=1);

namespace Awcodes\Mason\Tiptap\Nodes;

use Tiptap\Core\Node;

class RenderedMasonBrick extends Node
{
    public static $name = 'renderedBrick';

    public function renderHTML($node): array
    {
        return ['content' => $node->html];
    }
}
