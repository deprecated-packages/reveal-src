<?php

declare(strict_types=1);

namespace Reveal\LattePHPStanCompiler;

use Nette\Application\UI\Control;
use PhpParser\Node\Expr\Array_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Reveal\LattePHPStanCompiler\Contract\LatteVariableCollectorInterface;
use Reveal\LattePHPStanCompiler\Latte\Tokens\PhpToLatteLineNumbersResolver;
use Reveal\LattePHPStanCompiler\ValueObject\ComponentNameAndType;
use Reveal\TemplatePHPStanCompiler\TypeAnalyzer\TemplateVariableTypesResolver;
use Reveal\TemplatePHPStanCompiler\ValueObject\PhpFileContentsWithLineMap;
use Reveal\TemplatePHPStanCompiler\ValueObject\VariableAndType;

/**
 * @api
 */
final class TemplateFileVarTypeDocBlocksDecorator
{
    /**
     * @param LatteVariableCollectorInterface[] $latteVariableCollectors
     */
    public function __construct(
        private LatteToPhpCompiler $latteToPhpCompiler,
        private PhpToLatteLineNumbersResolver $phpToLatteLineNumbersResolver,
        private TemplateVariableTypesResolver $templateVariableTypesResolver,
        private array $latteVariableCollectors
    ) {
    }

    /**
     * @param ComponentNameAndType[] $componentNamesAndTypes
     */
    public function decorate(
        string $latteFilePath,
        Array_ $array,
        Scope $scope,
        array $componentNamesAndTypes
    ): PhpFileContentsWithLineMap {
        $variablesAndTypes = $this->resolveLatteVariablesAndTypes($array, $scope);

        $phpContent = $this->latteToPhpCompiler->compileFilePath(
            $latteFilePath,
            $variablesAndTypes,
            $componentNamesAndTypes
        );

        $phpLinesToLatteLines = $this->phpToLatteLineNumbersResolver->resolve($phpContent);
        return new PhpFileContentsWithLineMap($phpContent, $phpLinesToLatteLines);
    }

    /**
     * @return VariableAndType[]
     */
    private function resolveLatteVariablesAndTypes(Array_ $array, Scope $scope): array
    {
        // traverse nodes to add types after \DummyTemplateClass::main()
        $variablesAndTypes = $this->templateVariableTypesResolver->resolveArray($array, $scope);
        foreach ($this->latteVariableCollectors as $latteVariableCollector) {
            $collectedVariablesAndTypes = $latteVariableCollector->getVariablesAndTypes();
            $variablesAndTypes = array_merge($variablesAndTypes, $collectedVariablesAndTypes);
        }

        $classReflection = $scope->getClassReflection();
        if ($classReflection instanceof ClassReflection) {
            $objectType = new ObjectType($classReflection->getName());
            $variablesAndTypes[] = new VariableAndType('actualClass', $objectType);

            if ($objectType->isInstanceOf('Nette\Application\UI\Presenter')->yes()) {
                $variablesAndTypes[] = new VariableAndType('presenter', $objectType);
            }

            if ($objectType->isInstanceOf(Control::class)->yes()) {
                $variablesAndTypes[] = new VariableAndType('control', $objectType);
            }
        }

        return $variablesAndTypes;
    }
}
