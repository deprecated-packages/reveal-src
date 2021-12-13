<?php

declare(strict_types=1);

namespace Reveal\RevealNeon\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Include_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Registry;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use Reveal\RevealNeon\Generator\DependencyContainerAnalyzer;
use Reveal\RevealNeon\Registry\TypeFixerRegistry;
use Symplify\Astral\NodeValue\NodeValueResolver;

/**
 * @see \Reveal\RevealNeon\Tests\Rules\AnalyzeNeonRule\AnalyzeNeonRuleTest
 *
 * @implements Rule<Include_>
 */
final class AnalyzeNeonRule implements Rule
{
    private Registry $currentRegistry;

    /**
     * @param Rule[] $rules
     */
    public function __construct(
        private NodeValueResolver $nodeValueResolver,
        private DependencyContainerAnalyzer $dependencyContainerAnalyzer,
        array $rules
    ) {
        $this->currentRegistry = new TypeFixerRegistry($rules);
    }

    public function getNodeType(): string
    {
        return Include_::class;
    }

    /**
     * @param Include_ $node
     * @return string[]|RuleError[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->type !== Include_::TYPE_REQUIRE_ONCE) {
            return [];
        }

        if (! $node->expr instanceof Concat) {
            return [];
        }

        $filePath = $this->nodeValueResolver->resolveWithScope($node->expr, $scope);
        if (! is_string($filePath)) {
            return [];
        }

        if (! file_exists($filePath)) {
            return [];
        }

        return $this->dependencyContainerAnalyzer->analyseConfig($filePath, $this->currentRegistry);
    }
}
