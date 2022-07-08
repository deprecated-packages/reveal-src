<?php

declare(strict_types=1);

namespace Reveal\LattePHPStanCompiler\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\NodeVisitorAbstract;
use Reveal\LattePHPStanCompiler\Contract\LatteToPhpCompilerNodeVisitorInterface;
use Reveal\LattePHPStanCompiler\Contract\LinkProcessorInterface;
use Reveal\LattePHPStanCompiler\Exception\LattePHPStanCompilerException;
use Reveal\LattePHPStanCompiler\LinkProcessor\LinkProcessorFactory;
use Reveal\LattePHPStanCompiler\Nette\PresenterFactoryFaker;
use Reveal\TemplatePHPStanCompiler\ValueObject\VariableAndType;
use Symplify\Astral\Naming\SimpleNameResolver;
use Symplify\Astral\NodeValue\NodeValueResolver;

final class LinkNodeVisitor extends NodeVisitorAbstract implements LatteToPhpCompilerNodeVisitorInterface
{
    /**
     * @var VariableAndType[]
     */
    private array $variablesAndTypes = [];

    private ?VariableAndType $actualclass = null;

    public function __construct(
        private SimpleNameResolver $simpleNameResolver,
        private NodeValueResolver $nodeValueResolver,
        private LinkProcessorFactory $linkProcessorFactory,
        private PresenterFactoryFaker $presenterFactoryFaker,
    ) {
    }

    /**
     * @param VariableAndType[] $variablesAndTypes
     */
    public function setVariablesAndTypes(array $variablesAndTypes): void
    {
        $this->variablesAndTypes = $variablesAndTypes;
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

    /**
     * @return Node[]|null
     */
    public function leaveNode(Node $node): ?array
    {
        if (! $node instanceof Echo_) {
            return null;
        }

        $staticCall = $node->exprs[0] ?? null;
        if (! $staticCall instanceof StaticCall) {
            return null;
        }

        if (count($staticCall->getArgs()) !== 1) {
            return null;
        }

        $arg = $staticCall->getArgs()[0];

        $methodCall = $arg->value;
        if (! $methodCall instanceof MethodCall) {
            return null;
        }

        if (! $this->isMethodCallUiLink($methodCall)) {
            return null;
        }

        return $this->prepareNodes($methodCall, $node->getAttributes());
    }

    /**
     * @param array<string, mixed> $attributes
     * @return Node[]|null
     */
    private function prepareNodes(MethodCall $methodCall, array $attributes): ?array
    {
        $linkArgs = $methodCall->getArgs();
        $target = $linkArgs[0]->value;

        $targetName = $this->nodeValueResolver->resolve($target, '');
        if (! is_string($targetName)) {
            throw new LattePHPStanCompilerException();
        }

        $targetName = $this->remapTarget($targetName);
        $linkProcessor = $this->linkProcessorFactory->create($targetName);
        if (! $linkProcessor instanceof LinkProcessorInterface) {
            return null;
        }

        $targetParams = isset($linkArgs[1]) ? $linkArgs[1]->value : null;
        $linkParams = $targetParams instanceof Array_ ? $this->createLinkParams($targetParams) : [];

        $expressions = $linkProcessor->createLinkExpressions($targetName, $linkParams, $attributes);
        if ($expressions === []) {
            return null;
        }

        return $expressions;
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

    /**
     * @return Arg[]
     */
    private function createLinkParams(Array_ $array): array
    {
        $linkParams = [];
        foreach ($array->items as $arrayItem) {
            if (! $arrayItem instanceof ArrayItem) {
                continue;
            }

            $linkParams[] = new Arg($arrayItem);
        }

        return $linkParams;
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

        if ($this->actualclass === null) {
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
}
