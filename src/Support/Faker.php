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

    public function brick(string $id, array $config): static
    {
        $this->output['content'][] = [
            'type' => 'masonBrick',
            'attrs' => [
                'config' => $config,
                'id' => $id,
            ],
        ];

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
