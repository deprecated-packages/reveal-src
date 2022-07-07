<?php

declare(strict_types=1);

namespace Reveal\TemplatePHPStanCompiler\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitorAbstract;
use Symplify\Astral\Naming\SimpleNameResolver;

/**
 * @api
 */
final class TemplateVariableCollectingNodeVisitor extends NodeVisitorAbstract
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
     * @param array<string> $renderMethodNames
     */
    public function __construct(
        private array $defaultVariableNames,
        private array $renderMethodNames,
        private SimpleNameResolver $simpleNameResolver,
        private NodeFinder $nodeFinder,
    ) {
    }

    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function beforeTraverse(array $nodes): array
    {
        // reset to avoid used variable name in next analysed file
        $this->userVariableNames = [];
        $this->justCreatedVariableNames = [];

        return $nodes;
    }

    public function enterNode(Node $node): Node|null
    {
        if (! $node instanceof ClassMethod) {
            return null;
        }

        if (! $this->simpleNameResolver->isNames($node, $this->renderMethodNames)) {
            return null;
        }

        $this->userVariableNames = array_merge($this->userVariableNames, $this->resolveClassMethodVariableNames($node));
        return null;
    }

    /**
     * @return string[]
     */
    public function getUsedVariableNames(): array
    {
        $removedVariableNames = array_merge($this->defaultVariableNames, $this->justCreatedVariableNames);

        return array_diff($this->userVariableNames, $removedVariableNames);
    }

    /**
     * @return string[]
     */
    private function resolveClassMethodVariableNames(ClassMethod $classMethod): array
    {
        $variableNames = [];

        /** @var Variable[] $variables */
        $variables = $this->nodeFinder->findInstanceOf((array) $classMethod->stmts, Variable::class);

        $assignCreatedVariableNames = $this->resolveJustCreatedVariableNamesFromAssigns($classMethod);
        $foreachCreatedVariableNames = $this->resolveJustCreatedVariableNamesFromForeach($classMethod);

        $createdVariableNames = array_merge($assignCreatedVariableNames, $foreachCreatedVariableNames);

        foreach ($variables as $variable) {
            $variableName = $this->simpleNameResolver->getName($variable);
            if ($variableName === null) {
                continue;
            }

            if (in_array($variableName, $justCreatedVariableNames, true)) {
                continue;
            }

            $variableNames[] = $variableName;
        }

        $this->justCreatedVariableNames = $justCreatedVariableNames;

        return $variableNames;
    }

//    private function isJustCreatedVariable(Assign $assign): bool
//    {
//        if (! $parent instanceof Foreach_) {
//            return false;
//        }
//
//        return $parent->valueVar === $variable;
//    }

    /**
     * @return string[]
     */
    private function resolveJustCreatedVariableNamesFromAssigns(ClassMethod $classMethod): array
    {
        $variableNames = [];

        /** @var Assign[] $variables */
        $assigns = $this->nodeFinder->findInstanceOf((array)$classMethod->stmts, Assign::class);

        foreach ($assigns as $assign) {
            if (!$assign->var instanceof Variable) {
                continue;
            }

            $variableName = $this->simpleNameResolver->getName($assign->var);
            if ($variableName === null) {
                continue;
            }

            $variableNames[] = $variableName;
        }

        return $variableNames;
    }

    /**
     * @return string[]
     */
    private function resolveJustCreatedVariableNamesFromForeach(ClassMethod $classMethod): array
    {
        $variableNames = [];

        /** @var Foreach_[] $foreaches */
        $foreaches = $this->nodeFinder->findInstanceOf((array)$classMethod->stmts, Foreach_::class);

        foreach ($foreaches as $foreach) {
            if (! $foreach->valueVar instanceof Variable) {
                continue;
            }

            $variableName = $this->simpleNameResolver->getName($foreach->valueVar);
            if ($variableName === null) {
                continue;
            }

            $variableNames[] = $variableName;
        }

        return $variableNames;
    }
}
