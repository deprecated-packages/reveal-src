<?php

declare(strict_types=1);

namespace Reveal\RevealLatte\Contract;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use Reveal\LattePHPStanCompiler\ValueObject\ComponentNameAndType;
use Reveal\TemplatePHPStanCompiler\ValueObject\RenderTemplateWithParameters;

interface LatteTemplateHolderInterface
{
    /**
     * call before other methods
     */
    public function check(Node $node, Scope $scope): bool;

    /**
     * @return RenderTemplateWithParameters[]
     */
    public function findRenderTemplateWithParameters(Node $node, Scope $scope): array;

    /**
     * @return ComponentNameAndType[]
     */
    public function findComponentNamesAndTypes(Node $node, Scope $scope): array;
}
