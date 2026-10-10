<?php

declare(strict_types=1);

namespace Awcodes\Mason\Support;

use Awcodes\Mason\Concerns\HasBricks;
use Closure;
use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

class MasonRenderer implements Htmlable
{
    use EvaluatesClosures;
    use HasBricks;
    use Macroable;

    /**
     * @var string | array<string, mixed>
     */
    protected string | array | null $content = null;

    /**
     * @var array<string, mixed> | Closure
     */
    protected array | Closure $data = [];

    /**
     * @param  string | array<string, mixed> | null  $content
     */
    public function __construct(string | array | null $content = null)
    {
        if (is_array($content) && array_key_exists('content', $content)) {
            $content = $content['content'];
        }

        $this->content($content);
    }

    /**
     * @param  string | array<string, mixed> | null  $content
     */
    public static function make(string | array | null $content = null): static
    {
        return app(static::class, [
            'content' => $content,
        ]);
    }

    /**
     * @param  string | array<string, mixed> | null  $content
     */
    public function content(string | array | null $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Arbitrary context handed to every brick's toHtml() as its second argument.
     * Pass the record under a 'record' key to render content that depends on it.
     *
     * @param  array<string, mixed> | Closure  $data
     */
    public function data(array | Closure $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->evaluate($this->data) ?? [];
    }

    public function toUnsafeHtml(): string
    {
        return $this->renderBlocks($this->getBlocks());
    }

    public function toHtml(): string
    {
        return Str::sanitizeHtml($this->toUnsafeHtml());
    }

    public function toText(): string
    {
        $html = $this->toUnsafeHtml();

        // Strip HTML tags and decode entities
        return strip_tags($html);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->getBlocks();
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<int, mixed>  $content  the bricks inside a container brick
     */
    public function getBrickHtml(string $id, array $config, array $content = []): string
    {
        // getBrick() resolves through getCachedBricks(), which unwraps BrickGroups.
        // Walking getBricks() directly and filtering on is_string() skipped every
        // brick registered inside a group, so grouped bricks rendered as nothing.
        $brick = $this->getBrick($id);

        if ($brick === null) {
            // Missing bricks are silently ignored when rendering content directly
            return '';
        }

        $container = $this->getContainerBrick($id);

        if ($container !== null) {
            $children = array_values(array_filter(
                $content,
                fn (mixed $block): bool => is_array($block) && $this->canContainBrick($id, (string) ($block['attrs']['id'] ?? '')),
            ));

            return (string) $container::toHtmlWithChildren($config, new BrickChildren(
                array_map(fn (array $block): BrickChild => new BrickChild($block, $this->renderBlocks([$block])), $children),
            ), data: $this->getData());
        }

        return (string) $brick::toHtml($config, data: $this->getData());
    }

    /**
     * @param  array<int, mixed>  $blocks
     */
    protected function renderBlocks(array $blocks): string
    {
        $html = [];

        foreach ($blocks as $block) {
            if (! is_array($block) || ($block['type'] ?? null) !== 'masonBrick') {
                continue;
            }

            $id = $block['attrs']['id'] ?? null;
            $config = $block['attrs']['config'] ?? [];

            if (blank($id)) {
                continue;
            }

            $brickHtml = $this->getBrickHtml($id, $config, is_array($block['content'] ?? null) ? $block['content'] : []);

            if ($brickHtml !== '' && $brickHtml !== '0') {
                $html[] = $brickHtml;
            }
        }

        return implode('', $html);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getBlocks(): array
    {
        if (in_array($this->content, ['', '0', []], true) || $this->content === null) {
            return [];
        }

        if (is_array($this->content)) {
            return $this->content;
        }

        // Try to decode JSON if it's a string
        if (is_string($this->content)) {
            $decoded = json_decode($this->content, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }
}
