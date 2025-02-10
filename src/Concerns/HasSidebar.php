<?php

namespace Awcodes\Mason\Concerns;

use Closure;
use Filament\Forms\Components\Actions\Action;

trait HasSidebar
{
    protected array | Closure | null $sidebarActions = null;

    protected bool | Closure | null $isSidebarHidden = null;

    /**
     * @param  array<Action> | Closure  $actions
     */
    public function sidebar(array | Closure $actions): static
    {
        $this->sidebarActions = $actions;

        return $this;
    }

    public function hiddenSidebar(bool | Closure $condition = true): static
    {
        $this->isSidebarHidden = $condition;

        return $this;
    }

    /**
     * @return array<Action>
     */
    public function getSidebarActions(): array
    {
        return $this->evaluate($this->sidebarActions) ?? [];
    }

    public function isSidebarHidden(): bool
    {
        return $this->evaluate($this->isSidebarHidden) ?? false;
    }
}
