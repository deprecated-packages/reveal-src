<?php

declare(strict_types=1);

namespace Reveal\TemplatePHPStanCompiler\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\NodeVisitorAbstract;
use Symplify\Astral\Naming\SimpleNameResolver;
use Symplify\Astral\ValueObject\AttributeKey;

/**
 * @api
 */
final class VariableCollectingNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var string[]
     */
    private array $userVariableNames = [];

    /**
     * @var string[]
     */
    private array $justCreatedVariableNames = [];

    /**
     * @param array<string> $defaultVariableNames
     */
    public function __construct(
        private array $defaultVariableNames,
        private SimpleNameResolver $simpleNameResolver,
    ) {
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
        if (! $node instanceof Variable) {
            return null;
        }

        $variableName = $this->simpleNameResolver->getName($node);
        if ($variableName === null) {
            return null;
        }

        if ($this->isJustCreatedVariable($node)) {
            $this->justCreatedVariableNames[] = $variableName;
            return null;
        }

        $this->userVariableNames[] = $variableName;

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

    private function isJustCreatedVariable(Variable $variable): bool
    {
        $parent = $variable->getAttribute(AttributeKey::PARENT);
        if ($parent instanceof Assign && $parent->var === $variable) {
            return true;
        }

        if (! $parent instanceof Foreach_) {
            return false;
        }

        return $parent->valueVar === $variable;
    }
}
