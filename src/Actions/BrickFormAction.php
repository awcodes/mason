<?php

declare(strict_types=1);

namespace Awcodes\Mason\Actions;

use Closure;
use Filament\Actions\Action;

/**
 * The action behind the brick form. It opens the form at a part clicked in the preview after the action's own
 * afterFormFilled(), so that a brick or the field setting one does not turn parts off.
 */
class BrickFormAction extends Action
{
    protected ?Closure $revealPartUsing = null;

    public function revealPartUsing(?Closure $callback): static
    {
        $this->revealPartUsing = $callback;

        return $this;
    }

    public function callAfterFormFilled(): mixed
    {
        $result = parent::callAfterFormFilled();

        $this->evaluate($this->revealPartUsing);

        return $result;
    }
}
