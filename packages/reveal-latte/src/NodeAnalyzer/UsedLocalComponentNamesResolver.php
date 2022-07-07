<?php

declare(strict_types=1);

namespace Reveal\RevealLatte\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Symplify\Astral\Naming\SimpleNameResolver;
use Symplify\Astral\Reflection\ReflectionParser;

final class UsedLocalComponentNamesResolver
{
    public function __construct(
        private SimpleNameResolver $simpleNameResolver,
        private NodeFinder $nodeFinder,
        private ReflectionParser $reflectionParser
    ) {
    }

    /**
     * @return string[]
     */
    public function resolveFromClassMethod(ClassMethod $classMethod, Scope $scope): array
    {
        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return [];
        }

        $class = $this->reflectionParser->parseClassReflection($classReflection);
        if (! $class instanceof Class_) {
            return [];
        }

        $getComponentNames = $this->resolveThisGetComponentArguments($class);
        $dimFetchNames = $this->resolveDimFetchArguments($class);

        return array_merge($getComponentNames, $dimFetchNames);
    }

    /**
     * @return string[]
     */
    private function resolveThisGetComponentArguments(Class_ $class): array
    {
        $componentNames = [];

        /** @var MethodCall[] $methodCalls */
        $methodCalls = $this->nodeFinder->findInstanceOf($class, MethodCall::class);

        foreach ($methodCalls as $methodCall) {
            if (! $methodCall->var instanceof Variable) {
                continue;
            }

            if (! $this->simpleNameResolver->isName($methodCall->var, 'this')) {
                continue;
            }

            if (! $this->simpleNameResolver->isName($methodCall->name, 'getComponent')) {
                continue;
            }

            $firstArg = $methodCall->args[0] ?? null;
            if (! $firstArg instanceof Arg) {
                continue;
            }

            $firstArgValue = $firstArg->value;
            if (! $firstArgValue instanceof String_) {
                continue;
            }

            $componentNames[] = $firstArgValue->value;
        }

        return $componentNames;
    }

    /**
     * @return string[]
     */
    private function resolveDimFetchArguments(Class_ $class): array
    {
        $componentNames = [];

        /** @var ArrayDimFetch[] $arrayDimFetches */
        $arrayDimFetches = $this->nodeFinder->findInstanceOf($class, ArrayDimFetch::class);
        foreach ($arrayDimFetches as $arrayDimFetch) {
            if (! $arrayDimFetch->var instanceof Variable) {
                continue;
            }

            if (! $this->simpleNameResolver->isName($arrayDimFetch->var, 'this')) {
                continue;
            }

            if (! $arrayDimFetch->dim instanceof String_) {
                continue;
            }

            $componentNames[] = $arrayDimFetch->dim->value;
        }

        return $componentNames;
    }
}
