<?php

declare(strict_types=1);

namespace Awcodes\Mason\Concerns;

use Awcodes\Mason\Enums\SidebarPosition;
use Closure;
use Filament\Actions\Action;

trait HasSidebar
{
    protected array | Closure | null $sidebarActions = null;

    protected SidebarPosition | Closure | null $sidebarPosition = null;

    protected bool | Closure | null $hasGridActions = null;

    protected bool | Closure | null $hasOutline = null;

    /**
     * @param  array<Action> | Closure  $actions
     */
    public function sidebar(array | Closure $actions): static
    {
        $this->sidebarActions = $actions;

        return $this;
    }

    public function sidebarPosition(SidebarPosition | Closure | null $position = null): static
    {
        $this->sidebarPosition = $position;

        return $this;
    }

    public function displayActionsAsGrid(bool | Closure $condition = true): static
    {
        $this->hasGridActions = $condition;

        return $this;
    }

    public function outline(bool | Closure $condition = true): static
    {
        $this->hasOutline = $condition;

        return $this;
    }

    /**
     * @return array<Action>
     */
    public function getSidebarActions(): array
    {
        return $this->evaluate($this->sidebarActions) ?? [];
    }

    public function getSidebarPosition(): SidebarPosition
    {
        return $this->evaluate($this->sidebarPosition) ?? SidebarPosition::End;
    }

    public function hasGridActions(): bool
    {
        return $this->evaluate($this->hasGridActions) ?? false;
    }

    public function hasOutline(): bool
    {
        return $this->evaluate($this->hasOutline) ?? false;
    }
}
