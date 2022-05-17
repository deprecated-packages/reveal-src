<?php

declare(strict_types=1);

namespace Reveal\TemplatePHPStanCompiler\Rules;

use PhpParser\Node;
use PHPStan\Rules\Registry;
use PHPStan\Rules\Rule;
use Symplify\PHPStanRules\Rules\ForbiddenFuncCallRule;
use Symplify\PHPStanRules\Rules\NoDynamicNameRule;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;

/**
 * @api
 */
final class TemplateRulesRegistry extends Registry
{
    /**
     * @var array<class-string<DocumentedRuleInterface>>
     */
    private const EXCLUDED_RULES = [ForbiddenFuncCallRule::class, NoDynamicNameRule::class];

    /**
     * @param array<Rule<Node>> $rules
     */
    public function __construct(array $rules)
    {
        $activeRules = $this->filterActiveRules($rules);
        parent::__construct($activeRules);
    }

    /**
     * @template TNode as \PhpParser\Node
     * @param class-string<TNode> $nodeType
     * @return array<Rule<TNode>>
     */
    public function getRules(string $nodeType): array
    {
        return parent::getRules($nodeType);
    }

    /**
     * @param array<Rule<Node>> $rules
     * @return array<Rule<Node>>
     */
    private function filterActiveRules(array $rules): array
    {
        $activeRules = [];

        foreach ($rules as $rule) {
            foreach (self::EXCLUDED_RULES as $excludedRule) {
                if (is_a($rule, $excludedRule, true)) {
                    continue 2;
                }
            }

            $activeRules[] = $rule;
        }

        return $activeRules;
    }
}
