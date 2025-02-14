<?php

namespace Awcodes\Mason\Support;

use Awcodes\Mason\Tiptap\Nodes\MasonBrick;
use Filament\Support\Concerns\EvaluatesClosures;
use stdClass;
use Stevebauman\Hypertext\Transformer;
use Tiptap\Editor;
use Tiptap\Nodes\Document;
use Tiptap\Nodes\Paragraph;
use Tiptap\Nodes\Text;

class Converter
{
    use EvaluatesClosures;

    public function __construct(
        public string | array | stdClass $content,
        protected ?Editor $editor = null,
        protected ?array $bricks = null
    ) {
        if ($this->content instanceof stdClass) {
            $this->content = json_decode(json_encode($this->content), true);
        }
    }

    public function getEditor(): Editor
    {
        return $this->editor ??= new Editor([
            'extensions' => [
                new Document(['options' => ['content' => 'block*']]),
                new Text,
                new Paragraph,
                new MasonBrick(['bricks' => $this->bricks]),
            ],
        ]);
    }

    public function bricks(array $bricks): static
    {
        $this->bricks = $bricks;

        return $this;
    }

    public function sanitizeBricks(Editor $editor): Editor
    {
        $editor->descendants(function (&$node) {
            if ($node->type !== 'masonBrick') {
                return;
            }

            unset($node->content);
        });

        return $editor;
    }

    public function toHtml(): string
    {
        if ($this->validateContent()) {
            return '';
        }

        $editor = $this->getEditor()->setContent($this->content);

        return $editor->getHTML();
    }

    public function toJson(): array
    {
        if ($this->validateContent()) {
            return [];
        }

        $editor = $this->getEditor()->setContent($this->content);

        $this->sanitizeBricks($editor);

        return json_decode($editor->getJSON(), true);
    }

    public function toText(): string
    {
        if ($this->validateContent()) {
            return '';
        }

        return $this->getEditor()->setContent($this->content)
            ->descendants(function (&$node) {
                if ($node->type !== 'masonBrick') {
                    return;
                }

                $node->content = [];

                $brickData = json_decode(json_encode($node->attrs->values), true);
                $view = view($node->attrs->path, $brickData)->toHtml();

                $content = new stdClass;
                $content->type = 'text';
                $content->text = (new Transformer)->toText($view);
                $node->content[] = $content;
            })
            ->getText();
    }

    private function validateContent(): bool
    {
        return blank($this->content) || $this->content === '';
    }
}
