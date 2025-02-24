<?php

namespace Awcodes\Mason;

use Filament\Forms\Components\Actions\Action;
use Illuminate\Support\Js;

class Brick extends Action
{
    public function getAlpineClickHandler(): ?string
    {
        return $this->evaluate($this->alpineClickHandler) ?? '$wire.mountFormComponentAction(\'' . $this->component->getStatePath() . '\', \'' . $this->getName() . '\', { ...getEditor().getAttributes(\'' . $this->getName() . '\'), editorSelection }, ' . Js::from(['schemaComponent' => $this->component->getKey()]) . ')';
    }
}
