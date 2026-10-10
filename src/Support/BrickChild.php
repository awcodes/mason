<?php

declare(strict_types=1);

namespace Awcodes\Mason\Support;

use Illuminate\Contracts\Support\Htmlable;
use Stringable;

/**
 * A rendered brick inside a container brick. In the editor its HTML includes the brick's controls.
 */
class BrickChild implements Htmlable, Stringable
{
    /**
     * @param  array<string, mixed>  $block
     */
    public function __construct(
        protected array $block,
        protected string $html,
    ) {}

    public function __toString(): string
    {
        return $this->html;
    }

    public function getId(): ?string
    {
        return $this->block['attrs']['id'] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return is_array($this->block['attrs']['config'] ?? null) ? $this->block['attrs']['config'] : [];
    }

    /**
     * An attribute of the stored brick, e.g. layout data that only the container interprets.
     */
    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return data_get($this->block['attrs'] ?? [], $key, $default);
    }

    public function toHtml(): string
    {
        return $this->html;
    }
}
