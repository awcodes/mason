<?php

declare(strict_types=1);

namespace Awcodes\Mason\Support;

use Awcodes\Mason\Concerns\HasBricks;
use Filament\Support\Concerns\EvaluatesClosures;

class IframeRenderer
{
    use EvaluatesClosures;
    use HasBricks;

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    public function __construct(protected array $blocks = [])
    {
        if (app()->bound('debugbar')) {
            app('debugbar')->disable();
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    public static function make(array $blocks = []): static
    {
        return app(static::class, ['blocks' => $blocks]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    public function setBlocks(array $blocks): static
    {
        $this->blocks = $blocks;

        return $this;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }

    /**
     * Get the HTML for a single block
     *
     * @param  array<string, mixed>  $block
     * @param  string  $path  where the block sits, for the controls of the bricks inside it
     */
    public function getBlockHtml(array $block, string $path = ''): ?string
    {
        if (($block['type'] ?? null) !== 'masonBrick') {
            return null;
        }

        $id = $block['attrs']['id'] ?? null;
        $config = $block['attrs']['config'] ?? [];

        if (blank($id)) {
            return null;
        }

        // getBrick() resolves through getCachedBricks(), which unwraps BrickGroups.
        // Walking getBricks() directly skipped every brick registered inside a
        // group, and fatalled outright once a group reached the brick list.
        $brick = $this->getBrick($id);

        if ($brick === null) {
            return view('mason::components.unregistered-brick', ['label' => $id])->render();
        }

        $container = $this->getContainerBrick($id);

        if ($container !== null) {
            return $container::toHtmlWithChildren($config, $this->getChildren($block, $path));
        }

        return $brick::toHtml($config);
    }

    /**
     * Render the full iframe HTML document
     */
    public function toHtml(?string $layout = null): string
    {
        $blocks = array_map(
            fn (array $block, int $index): array => $this->getBlockData($block, (string) $index),
            $this->blocks,
            array_keys($this->blocks),
        );

        // Use the provided layout, fallback to config, then default
        $layoutToUse = $layout ?? config('mason.preview.layout');

        // If a layout is configured, use it with the preview content slotted in
        if ($layoutToUse) {
            return view($layoutToUse, [
                'blocks' => $blocks,
            ])->render();
        }

        // Otherwise, use the default full HTML document
        return view('mason::iframe-preview', [
            'blocks' => $blocks,
        ])->render();
    }

    /**
     * The bricks inside a container, each wrapped in the editor's controls, and the zone to drop more into it.
     *
     * @param  array<string, mixed>  $block
     */
    protected function getChildren(array $block, string $path): BrickChildren
    {
        $content = array_values(array_filter(
            is_array($block['content'] ?? null) ? $block['content'] : [],
            fn (mixed $child): bool => is_array($child) && ($child['type'] ?? null) === 'masonBrick',
        ));

        $children = array_map(function (array $child, int $index) use ($path, $content): BrickChild {
            $childPath = ltrim($path . '.' . $index, '.');

            return new BrickChild($child, view('mason::components.preview-block', [
                'block' => $this->getBlockData($child, $childPath),
                'total' => count($content),
            ])->render());
        }, $content, array_keys($content));

        return new BrickChildren($children, view('mason::components.drop-zone', [
            'parent' => $path,
            'index' => count($content),
            'isEmpty' => $content === [],
        ])->render());
    }

    /**
     * What the preview views need to render a block with its controls.
     *
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>
     */
    protected function getBlockData(array $block, string $path): array
    {
        $id = $block['attrs']['id'] ?? null;

        return [
            'index' => (int) str($path)->afterLast('.')->toString(),
            'path' => $path,
            'id' => $id,
            'config' => $block['attrs']['config'] ?? [],
            'html' => $this->getBlockHtml($block, $path),
            'label' => $this->getBlockLabel($id),
            'accepts' => $this->isContainerBrick($id) ? $this->getAcceptedBrickIds($id) : null,
        ];
    }

    /**
     * Get the label for a block
     */
    protected function getBlockLabel(?string $id): string
    {
        if (blank($id)) {
            return 'Unknown Brick';
        }

        $brick = $this->getBrick($id);

        if ($brick === null) {
            return 'Unknown Brick';
        }

        return $brick::getLabel();
    }
}
