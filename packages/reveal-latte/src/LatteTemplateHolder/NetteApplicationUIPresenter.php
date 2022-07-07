<?php

declare(strict_types=1);

namespace Reveal\RevealLatte\LatteTemplateHolder;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Type\ObjectType;
use Reveal\LattePHPStanCompiler\ValueObject\ComponentNameAndType;
use Reveal\RevealLatte\Contract\LatteTemplateHolderInterface;
use Reveal\RevealLatte\NodeAnalyzer\LatteTemplateWithParametersMatcher;
use Reveal\RevealLatte\TypeAnalyzer\ComponentMapResolver;
use Reveal\TemplatePHPStanCompiler\ValueObject\RenderTemplateWithParameters;
use Symplify\Astral\Naming\SimpleNameResolver;

final class NetteApplicationUIPresenter implements LatteTemplateHolderInterface
{
    public function __construct(
        private SimpleNameResolver $simpleNameResolver,
        private LatteTemplateWithParametersMatcher $latteTemplateWithParametersMatcher,
        private ComponentMapResolver $componentMapResolver,
    ) {
    }

    public function check(Node $node, Scope $scope): bool
    {
        if (! $node instanceof InClassNode) {
            return false;
        }

        $class = $node->getOriginalNode();
        if (! $class instanceof Class_) {
            return false;
        }

        $className = $this->simpleNameResolver->getName($class);
        if (! $className) {
            return false;
        }

        $objectType = new ObjectType($className);
        return $objectType->isInstanceOf('Nette\Application\UI\Presenter')
            ->yes();
    }

    /**
     * @param InClassNode $node
     * @return RenderTemplateWithParameters[]
     */
    public function findRenderTemplateWithParameters(Node $node, Scope $scope): array
    {
        /** @var Class_ $class */
        $class = $node->getOriginalNode();
        $methods = $class->getMethods();

        $templatesAndParameters = [];
        foreach ($methods as $method) {
            if (! $this->simpleNameResolver->isNames($method, ['render*', 'action*'])) {
                continue;
            }

            $template = $this->findTemplateFilePath($method, $scope);
            if ($template === null) {
                continue;
            }

            $parameters = $this->latteTemplateWithParametersMatcher->findParameters($method, $scope);
            if (! isset($templatesAndParameters[$template])) {
                $templatesAndParameters[$template] = [];
            }

            $templatesAndParameters[$template] = array_merge($templatesAndParameters[$template], $parameters);
        }

        $renderTemplatesWithParameters = [];
        foreach ($templatesAndParameters as $template => $parameters) {
            $renderTemplatesWithParameters[] = new RenderTemplateWithParameters($template, new Array_($parameters));
        }

        return $renderTemplatesWithParameters;
    }

    /**
     * @param InClassNode $node
     * @return ComponentNameAndType[]
     */
    public function findComponentNamesAndTypes(Node $node, Scope $scope): array
    {
        /** @var Class_ $class */
        $class = $node->getOriginalNode();
        return $this->componentMapResolver->resolveComponentNamesAndTypes($class, $scope);
    }

    private function findTemplateFilePath(ClassMethod $classMethod, Scope $scope): ?string
    {
        $class = $this->simpleNodeFinder->findFirstParentByType($classMethod, Class_::class);
        if (! $class instanceof Class_) {
            return null;
        }

        $className = $this->simpleNameResolver->getName($class);
        if (! $className) {
            return null;
        }

        $shortClassName = $this->simpleNameResolver->resolveShortName($className);
        $presenterName = str_replace('Presenter', '', $shortClassName);

        $methodName = $this->simpleNameResolver->getName($classMethod);
        if (! $methodName) {
            return null;
        }

        $actionName = str_replace(['action', 'render'], '', $methodName);
        $actionName = lcfirst($actionName);

        $dir = dirname($scope->getFile());
        $dir = is_dir($dir . '/templates') ? $dir : dirname($dir);

        $templateFileCandidates = [
            $dir . '/templates/' . $presenterName . '/' . $actionName . '.latte',
            $dir . '/templates/' . $presenterName . '.' . $actionName . '.latte',
        ];

        foreach ($templateFileCandidates as $templateFileCandidate) {
            if (file_exists($templateFileCandidate)) {
                return $templateFileCandidate;
            }
        }

        return null;
    }
}
