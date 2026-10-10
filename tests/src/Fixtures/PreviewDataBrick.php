<?php

declare(strict_types=1);

namespace Awcodes\Mason\Tests\Fixtures;

use Awcodes\Mason\Brick;

/**
 * Shows the data it gets in the editor preview, or that it got none.
 */
class PreviewDataBrick extends Brick
{
    public static function getId(): string
    {
        return 'preview-data-brick';
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return '<div class="preview-data-brick">' . ($data === null ? 'no data' : e(json_encode($data))) . '</div>';
    }
}
