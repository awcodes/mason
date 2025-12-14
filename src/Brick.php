<?php

namespace Awcodes\Mason;

use Exception;
use Filament\Actions\Action;
use Illuminate\Support\Js;

class Brick extends Action
{
    /**
     * @throws Exception
     */
    public function getAlpineClickHandler(): ?string
    {
        return $this->evaluate($this->alpineClickHandler) ?? '$wire.mountFormComponentAction(\'' . $this->component->getStatePath() . '\', \'' . $this->getName() . '\', { ...getEditor().getAttributes(\'' . $this->getName() . '\'), editorSelection }, ' . Js::from(['schemaComponent' => $this->component->getKey()]) . ')';
    }
}
