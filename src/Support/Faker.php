<?php

namespace Awcodes\Mason\Support;

class Faker
{
    protected array $output = [];

    public function __construct()
    {
        $this->output = [
            'type' => 'doc',
            'content' => [],
        ];
    }

    public static function make(): self
    {
        return new self;
    }

    public function brick(string $identifier, string $path, ?array $values = []): static
    {
        $this->output['content'][] = [
            'type' => 'masonBrick',
            'attrs' => [
                'identifier' => $identifier,
                'path' => $path,
                'values' => $values,
            ],
        ];

        return $this;
    }

    public function asHtml(): string
    {
        return (new Converter($this->output))->toHtml();
    }

    public function asJson(): array
    {
        return (new Converter($this->output))->toJson();
    }

    public function asText(): string
    {
        return (new Converter($this->output))->toText();
    }
}
