<?php

declare(strict_types=1);

namespace Reveal\TemplatePHPStanCompiler\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Node\ClassMethod;
use Reveal\TemplatePHPStanCompiler\VariableUsage\CreatedVariableNamesResolver;
use Symplify\Astral\Naming\SimpleNameResolver;

/**
 * @api
 */
final class TwigVariableCollectingNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var string[]
     */
    private array $userVariableNames = [];

    /**
     * @var string[]
     */
    private array $justCreatedVariableNames = [];

    private CreatedVariableNamesResolver $createdVariableNamesResolver;

    /**
     * @param array<string> $defaultVariableNames
     */
    public function __construct(
        private array $defaultVariableNames,
        private SimpleNameResolver $simpleNameResolver,
    ) {
        $this->createdVariableNamesResolver = new CreatedVariableNamesResolver(new NodeFinder(), $simpleNameResolver);
    }

    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function beforeTraverse(array $nodes): array
    {
        $this->reset();

        return $nodes;
    }

    public function enterNode(Node $node): Node|null
    {
        if (! $node instanceof ClassMethod) {
            return null;
        }

        $this->justCreatedVariableNames = $this->createdVariableNamesResolver->resolve($node);

        $nodeFinder = new NodeFinder();
        $variables = $nodeFinder->findInstanceOf($node, Variable::class);

        foreach ($variables as $variable) {
            $variableName = $this->simpleNameResolver->getName($variable);
            if (! is_string($variableName)) {
                continue;
            }

            $this->userVariableNames[] = $variableName;
        }

        return null;
    }

    /**
     * @return string[]
     */
    public function getUsedVariableNames(): array
    {
        $removedVariableNames = array_merge($this->defaultVariableNames, $this->justCreatedVariableNames);

        $usedVariableNames = array_diff($this->userVariableNames, $removedVariableNames);

        return array_unique($usedVariableNames);
    }

    private function reset(): void
    {
        // reset to avoid used variable name in next analysed file
        $this->userVariableNames = [];
        $this->justCreatedVariableNames = [];
    }
}
