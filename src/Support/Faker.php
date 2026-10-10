<?php

declare(strict_types=1);

namespace Awcodes\Mason\Support;

class Faker
{
    protected array $output = [
        'type' => 'doc',
        'content' => [],
    ];

    public static function make(): self
    {
        return new self;
    }

    /**
     * @param  self|null  $children  the bricks inside a container brick
     */
    public function brick(string $id, array $config, ?self $children = null): static
    {
        $brick = [
            'type' => 'masonBrick',
            'attrs' => [
                'config' => $config,
                'id' => $id,
            ],
        ];

        if ($children instanceof self) {
            $brick['content'] = $children->output['content'];
        }

        $this->output['content'][] = $brick;

        return $this;
    }

    public function asHtml(): string
    {
        return (new MasonRenderer($this->output))->toHtml();
    }

    public function asJson(): array
    {
        return (new MasonRenderer($this->output))->toArray();
    }

    public function asText(): string
    {
        return (new MasonRenderer($this->output))->toText();
    }
}
