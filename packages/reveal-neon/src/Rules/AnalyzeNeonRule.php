<?php

declare(strict_types=1);

namespace Reveal\RevealNeon\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;

/**
 * @see \Reveal\RevealNeon\Tests\Rules\AnalyzeNeonRule\AnalyzeNeonRuleTest
 *
 * @implements Rule<Class_>
 */
final class AnalyzeNeonRule implements Rule
{
    public function getNodeType(): string
    {
        return Class_::class;
    }

    /**
     * @param Class_ $node
     * @return string[]|RuleError[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // ...
        die;

        return [];
    }
}
