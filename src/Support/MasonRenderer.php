<?php

declare(strict_types=1);

namespace Awcodes\Mason\Support;

use Awcodes\Mason\Concerns\HasBricks;
use Awcodes\Mason\Tiptap\Nodes\MasonBrick;
use Awcodes\Mason\Tiptap\Nodes\RenderedMasonBrick;
use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Tiptap\Core\Extension;
use Tiptap\Editor;
use Tiptap\Nodes\Document;
use Tiptap\Nodes\Paragraph;
use Tiptap\Nodes\Text;

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
     * @param  string | array<string, mixed> | null  $content
     */
    public function __construct(string | array | null $content = null)
    {
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
     * @return array<Extension>
     */
    public function getTipTapPhpExtensions(): array
    {
        return [
            app(Document::class, ['options' => ['content' => 'block*']]),
            app(Text::class),
            app(Paragraph::class),
            app(MasonBrick::class, ['bricks' => $this->getBricks()]),
            app(RenderedMasonBrick::class),
        ];
    }

    /**
     * @return array{extensions: array<Extension>}
     */
    public function getTipTapPhpConfiguration(): array
    {
        return [
            'extensions' => $this->getTipTapPhpExtensions(),
        ];
    }

    public function getEditor(): Editor
    {
        $editor = app(Editor::class, ['configuration' => $this->getTipTapPhpConfiguration()]);

        if (filled($this->content)) {
            $editor->setContent($this->content);
        }

        return $editor;
    }

    public function toUnsafeHtml(): string
    {
        $editor = $this->getEditor();

        $this->processBricks($editor);

        return $editor->getHTML();
    }

    public function toHtml(): string
    {
        return Str::sanitizeHtml($this->toUnsafeHtml());
    }

    public function toText(): string
    {
        $editor = $this->getEditor();

        $this->processBricks($editor);

        return $editor->getText();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        if (in_array($this->content, ['', '0', []], true) || $this->content === null) {
            return [];
        }

        $editor = $this->getEditor();
        $this->processBricks($editor);

        return json_decode($editor->getJSON(), true);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public function getBrickHtml(string $id, array $config): ?string
    {
        foreach ($this->bricks as $key => $brick) {
            if (is_string($key) && ($key::getId() === $id)) {
                return $key::toHtml($config, data: value($brick) ?? []);
            }
            if (is_string($brick) && ($brick::getId() === $id)) {
                return $brick::toHtml($config, data: []);
            }
        }

        return null;
    }

    protected function processBricks(Editor $editor): void
    {
        if (blank($this->bricks)) {
            return;
        }

        $editor->descendants(function (object &$node): void {
            if ($node->type !== 'masonBrick') {
                return;
            }

            if (blank($node->attrs->id ?? null)) {
                return;
            }

            $nodeConfig = json_decode(json_encode($node->attrs->config ?? []), associative: true);

            $node->type = 'renderedBrick';
            $node->html = $this->getBrickHtml($node->attrs->id, $nodeConfig);
            unset($node->attrs->config);
        });
    }
}
