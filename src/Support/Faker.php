<?php

namespace Awcodes\Mason\Support;

use Faker\Factory;
use Faker\Generator;

class Faker
{
    protected Generator $faker;

    protected string $output = '';

    public static function make(): static
    {
        $static = new static;
        $static->faker = Factory::create();

        return $static;
    }

    public function brick(string $identifier, string $path, ?array $values = []): static
    {
        $this->output .= '<mason-brick>' . json_encode(['identifier' => $identifier, 'path' => $path, 'values' => $values]) . '</mason-brick>';

        return $this;
    }

    public function asHtml(): string
    {
        return $this->output;
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
