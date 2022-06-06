<?php

declare(strict_types=1);

namespace Reveal\LattePHPStanCompiler\Contract;

use Reveal\TemplatePHPStanCompiler\ValueObject\VariableAndType;

interface LatteVariableCollectorInterface
{
    /**
     * @return VariableAndType[]
     */
    public function getVariablesAndTypes(): array;
}
