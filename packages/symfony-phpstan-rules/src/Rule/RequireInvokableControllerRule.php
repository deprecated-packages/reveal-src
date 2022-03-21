<?php

declare(strict_types=1);

namespace Reveal\SymfonyPHPStanRules\Rule;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;

/**
 * @implements Rule<ClassMethod>
 *
 * @see \Reveal\SymfonyPHPStanRules\Tests\Rule\RequireInvokableControllerRule\RequireInvokableControllerRuleTest
 *
 * Simple port of https://github.com/symplify/phpstan-rules/blob/main/packages/symfony/src/Rules/RequireInvokableControllerRule.php with minimum dependencies
 */
final class RequireInvokableControllerRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Use invokable controller with __invoke() method instead of many named action methods';

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /**
     * @param ClassMethod $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->isPublic()) {
            return [];
        }

        $methodName = $node->name->toString();
        if ($methodName === '__invoke') {
            // nothing to check
            return [];
        }

        if (! $this->isInsideSymfonyController($scope)) {
            return [];
        }

        return [self::ERROR_MESSAGE];
    }

    private function isInsideSymfonyController(Scope $scope): bool
    {
        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        if ($classReflection->isSubclassOf('Symfony\Bundle\FrameworkBundle\Controller\AbstractController')) {
            return true;
        }

        return $classReflection->isSubclassOf('Symfony\Bundle\FrameworkBundle\Controller\Controller');
    }
}
