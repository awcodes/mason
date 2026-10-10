<?php

declare(strict_types=1);

namespace Awcodes\Mason\Support;

use ArrayIterator;
use Countable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use IteratorAggregate;
use Traversable;

/**
 * The bricks inside a container brick, rendered.
 *
 * A container's view either outputs them all at once or loops over them to wrap
 * each one, and should output dropZone() after them: in the editor that is where
 * bricks are dropped into the container, everywhere else it is empty.
 *
 * @implements IteratorAggregate<int, BrickChild>
 */
class BrickChildren implements Countable, Htmlable, IteratorAggregate
{
    /**
     * @param  list<BrickChild>  $children
     */
    public function __construct(
        protected array $children = [],
        protected string $dropZone = '',
    ) {}

    public function dropZone(): Htmlable
    {
        return new HtmlString($this->dropZone);
    }

    public function isEmpty(): bool
    {
        return $this->children === [];
    }

    public function count(): int
    {
        return count($this->children);
    }

    /**
     * @return Traversable<int, BrickChild>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->children);
    }

    public function toHtml(): string
    {
        return implode('', array_map(fn (BrickChild $child): string => $child->toHtml(), $this->children)) . $this->dropZone;
    }
}
