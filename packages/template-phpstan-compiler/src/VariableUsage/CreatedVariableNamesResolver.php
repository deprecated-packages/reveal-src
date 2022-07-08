<?php

declare(strict_types=1);

namespace Reveal\TemplatePHPStanCompiler\VariableUsage;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\NodeFinder;
use Symplify\Astral\Naming\SimpleNameResolver;

final class CreatedVariableNamesResolver
{
    public function __construct(
        private NodeFinder $nodeFinder,
        private SimpleNameResolver $simpleNameResolver,
    ) {
    }

    /**
     * @return string[]
     */
    public function resolve(ClassMethod $classMethod): array
    {
        $assignCreatedVariableNames = $this->resolveJustCreatedVariableNamesFromAssigns($classMethod);
        $foreachCreatedVariableNames = $this->resolveJustCreatedVariableNamesFromForeach($classMethod);

        return array_merge($assignCreatedVariableNames, $foreachCreatedVariableNames);
    }

    /**
     * @return string[]
     */
    private function resolveJustCreatedVariableNamesFromAssigns(ClassMethod $classMethod): array
    {
        $variableNames = [];

        /** @var Assign[] $assigns */
        $assigns = $this->nodeFinder->findInstanceOf((array) $classMethod->stmts, Assign::class);

        foreach ($assigns as $assign) {
            if (! $assign->var instanceof Variable) {
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
        $foreaches = $this->nodeFinder->findInstanceOf((array) $classMethod->stmts, Foreach_::class);

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
