<?php

declare(strict_types=1);

namespace Reveal\RevealTwig\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use Reveal\RevealTwig\NodeAnalyzer\SymfonyRenderWithParametersMatcher;
use Reveal\TwigPHPStanCompiler\NodeAnalyzer\MissingTwigTemplateRenderVariableResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @implements Rule<MethodCall>
 * @api
 * @see \Reveal\RevealTwig\Tests\Rules\NoTwigMissingVariableRule\NoTwigMissingVariableRuleTest
 */
final class NoTwigMissingVariableRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Variable "%s" is used in template but missing in render() method';

    public function __construct(
        private MissingTwigTemplateRenderVariableResolver $missingTwigTemplateRenderVariableResolver,
        private SymfonyRenderWithParametersMatcher $symfonyRenderWithParametersMatcher
    ) {
    }

    /**
     * @return class-string<Node>
     */
    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param MethodCall $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $renderTemplatesWithParameters = $this->symfonyRenderWithParametersMatcher->matchSymfonyRender($node, $scope);

        $missingVariableNames = [];
        foreach ($renderTemplatesWithParameters as $renderTemplateWithParameter) {
            $missingVariableNames = array_merge(
                $missingVariableNames,
                $this->missingTwigTemplateRenderVariableResolver->resolveFromTemplateAndMethodCall(
                    $renderTemplateWithParameter,
                    $scope
                )
            );
        }

        if ($missingVariableNames === []) {
            return [];
        }

        $errorMessages = [];
        foreach ($missingVariableNames as $missingVariableName) {
            $errorMessages[] = sprintf(self::ERROR_MESSAGE, $missingVariableName);
        }

        return $errorMessages;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class SomeController extends AbstractController
{
    public function __invoke()
    {
        return $this->render(__DIR__ . '/some_file.twig', [
            'non_existing_variable' => 'value'
        ]);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class SomeController extends AbstractController
{
    public function __invoke()
    {
        return $this->render(__DIR__ . '/some_file.twig', [
            'existing_variable' => 'value'
        ]);
    }
}
CODE_SAMPLE
            ),
        ]);
    }
}
