<?php

namespace Awcodes\Mason\Support;

use Awcodes\Mason\Mason;
use Filament\Schemas\Components\StateCasts\Contracts\StateCast;
use Illuminate\Contracts\Support\Htmlable;

class MasonStateCast implements StateCast
{
    public function __construct(
        protected Mason $mason,
    ) {}

    /**
     * @return string | array<string, mixed>
     */
    public function get(mixed $state): string | array
    {
        $editor = $this->mason->getTipTapEditor()
            ->setContent($state ?? [
                'type' => 'doc',
                'content' => [],
            ]);

        if ($this->mason->getBricks()) {
            $editor->descendants(function (object &$node): void {
                if ($node->type !== 'masonBrick') {
                    return;
                }

                unset($node->attrs->label);
                unset($node->attrs->preview);
            });
        }

        return $editor->{$this->mason->isJson() ? 'getDocument' : 'getHtml'}();
    }

    /**
     * @return array<string, mixed>
     */
    public function set(mixed $state): array
    {
        if ($state instanceof Htmlable) {
            $state = $state->toHtml();
        }

        $editor = $this->mason->getTipTapEditor()
            ->setContent($state ?? [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [],
                    ],
                ],
            ])
            ->descendants(function (object &$node): void {
                if ($node->type !== 'masonBrick') {
                    return;
                }

                $brick = $this->mason->getBrick($node->attrs->id);

                if (blank($brick)) {
                    return;
                }

                $nodeConfig = json_decode(json_encode($node->attrs->config ?? []), associative: true);

                $node->attrs->label = $brick::getPreviewLabel($nodeConfig);
                $node->attrs->preview = base64_encode($brick::toPreviewHtml($nodeConfig));
            });

        return $editor->getDocument();
    }
}
