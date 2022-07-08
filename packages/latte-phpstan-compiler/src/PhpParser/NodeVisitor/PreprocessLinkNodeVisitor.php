<?php

declare(strict_types=1);

namespace Reveal\LattePHPStanCompiler\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\NodeVisitorAbstract;
use Reveal\LattePHPStanCompiler\Exception\LattePHPStanCompilerException;
use Reveal\LattePHPStanCompiler\Nette\PresenterFactoryFaker;
use Reveal\TemplatePHPStanCompiler\ValueObject\VariableAndType;
use Symplify\Astral\Naming\SimpleNameResolver;
use Symplify\Astral\NodeValue\NodeValueResolver;

final class PreprocessLinkNodeVisitor extends NodeVisitorAbstract
{
    private ?VariableAndType $actualclass = null;

    /**
     * @param VariableAndType[] $variablesAndTypes
     */
    public function __construct(
        private SimpleNameResolver $simpleNameResolver,
        private NodeValueResolver $nodeValueResolver,
        private PresenterFactoryFaker $presenterFactoryFaker,
        private array $variablesAndTypes
    ) {
    }

    public function beforeTraverse(array $nodes)
    {
        // finding $actualClass
        foreach ($this->variablesAndTypes as $variableAndType) {
            if ($variableAndType->getVariable() === 'actualClass') {
                $this->actualclass = $variableAndType;
            }
        }
        return null;
    }

    public function enterNode(Node $node): ?Node
    {
        if ($this->actualclass === null) {
            return null;
        }

        $methodCall = $node;
        if (! $methodCall instanceof MethodCall) {
            return null;
        }

        if (! $this->isMethodCallUiLink($methodCall)) {
            return null;
        }

        $linkArgs = $methodCall->getArgs();
        $target = $linkArgs[0]->value;
        $targetName = $this->nodeValueResolver->resolve($target, '');
        if (! is_string($targetName)) {
            throw new LattePHPStanCompilerException();
        }

        $newTargetName = $this->remapTarget($targetName);
        $linkArgs[0] = new Node\Arg(new Node\Scalar\String_($newTargetName));
        $methodCall->args = $linkArgs;
        return $methodCall;
    }

    private function remapTarget(string $targetName): string
    {
        $targetName = ltrim($targetName, '/:');

        if (str_ends_with($targetName, '!')) {
            return $targetName;
        }

        $targetNameParts = explode(':', $targetName);
        if (count($targetNameParts) === 3) {
            return $targetName;
        }

        $presenterFactory = $this->presenterFactoryFaker->getPresenterFactory();
        $presenterName = @$presenterFactory->unformatPresenterClass($this->actualclass->getTypeAsString());

        if ($presenterName === null) {
            return $targetName;
        }

        if (count($targetNameParts) === 1) {
            return $presenterName . ':' . $targetName;
        }

        $presenterNameParts = explode(':', $presenterName, 2);

        $module = isset($presenterNameParts[1]) ? $presenterNameParts[0] : null;
        if ($module) {
            return $module . ':' . $targetName;
        }

        return $targetName;
    }

    private function isMethodCallUiLink(MethodCall $methodCall): bool
    {
        $methodName = $this->simpleNameResolver->getName($methodCall->name);
        if ($methodName !== 'link') {
            return false;
        }

        $propertyFetch = $methodCall->var;
        if (! $propertyFetch instanceof PropertyFetch) {
            return false;
        }

        $propertyFetchName = $this->simpleNameResolver->getName($propertyFetch->name);
        return in_array($propertyFetchName, ['uiControl', 'uiPresenter'], true);
    }
}
