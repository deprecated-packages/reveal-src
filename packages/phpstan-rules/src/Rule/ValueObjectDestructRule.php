<?php

declare(strict_types=1);

namespace Reveal\PHPStanRules\Rule;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Type\ObjectType;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @implements Rule<CallLike>
 *
 * @see \Reveal\PHPStanRules\Tests\Rule\ValueObjectDestructRule\ValueObjectDestructRuleTest
 */
final class ValueObjectDestructRule implements Rule, DocumentedRuleInterface
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Instead of calling all public methods of value object, pass it directly';

    /**
     * @var array<string, string[]>
     */
    private array $cachedClassPublicMethodNames = [];

    public function __construct(
        private ReflectionProvider $reflectionProvider
    ) {
    }

    public function getNodeType(): string
    {
        return CallLike::class;
    }

    /**
     * @param CallLike $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $args = $node->getArgs();
        if ($args === []) {
            return [];
        }

        $calledMethodsByType = $this->resolveCalledMethodsByType($args, $scope);

        foreach ($calledMethodsByType as $className => $methodNames) {
            if (! $this->reflectionProvider->hasClass($className)) {
                continue;
            }

            $methodNames = array_unique($methodNames);

            $publicMethodsNames = $this->resolveClassPublicMethodNames($className);

            if (array_intersect($methodNames, $publicMethodsNames) === $publicMethodsNames) {
                return [self::ERROR_MESSAGE];
            }
        }

        return [];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [
            new CodeSample(
                <<<'CODE_SAMPLE'
$person = new Name('Matthias');
$this->process($person->getName());
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$person = new Name('Matthias');
$this->process($person);
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    private function resolveClassPublicMethodNames(string $className): array
    {
        if (isset($this->cachedClassPublicMethodNames[$className])) {
            return $this->cachedClassPublicMethodNames[$className];
        }

        $classReflection = $this->reflectionProvider->getClass($className);
        $nativeClassReflection = $classReflection->getNativeReflection();

        $publicMethodsNames = [];
        foreach ($nativeClassReflection->getMethods() as $reflectionMethod) {
            if ($reflectionMethod->isConstructor()) {
                continue;
            }

            if (! $reflectionMethod->isPublic()) {
                continue;
            }

            $publicMethodsNames[] = $reflectionMethod->getName();
        }

        $this->cachedClassPublicMethodNames[$className] = $publicMethodsNames;

        return $publicMethodsNames;
    }

    /**
     * @param Arg[] $args
     * @return array<string, string[]>
     */
    private function resolveCalledMethodsByType(array $args, Scope $scope): array
    {
        $calledMethodsByType = [];

        foreach ($args as $arg) {
            if (! $arg->value instanceof MethodCall) {
                continue;
            }

            $methodCall = $arg->value;
            $callerType = $scope->getType($methodCall->var);
            if (! $callerType instanceof ObjectType) {
                continue;
            }

            if (! $methodCall->name instanceof Identifier) {
                continue;
            }

            $className = $callerType->getClassName();
            $methodName = $methodCall->name->toString();

            $calledMethodsByType[$className][] = $methodName;
        }

        return $calledMethodsByType;
    }
}
