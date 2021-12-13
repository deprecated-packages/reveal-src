<?php

declare(strict_types=1);

namespace Reveal\RevealNeon\Registry;

use PhpParser\Node;
use PHPStan\Rules\Classes\InstantiationRule;
use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Rules\Methods\CallMethodsRule;
use PHPStan\Rules\Registry;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\PHPStanRules\Rules\ForbiddenFuncCallRule;
use Symplify\PHPStanRules\Rules\NoDynamicNameRule;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;

final class TypeFixerRegistry extends Registry
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
        $activeRules = parent::getRules($nodeType);

        // only fix in a weird test case setup
        if (defined('PHPUNIT_COMPOSER_INSTALL')) {
            $privatesAccessor = new PrivatesAccessor();

            foreach ($activeRules as $activeRule) {
                if (! $activeRule instanceof CallMethodsRule && ! $activeRule instanceof InstantiationRule) {
                    continue;
                }

                if ($activeRule instanceof CallMethodsRule) {
                    /** @var FunctionCallParametersCheck $check */
                    $check = $privatesAccessor->getPrivateProperty($activeRule, 'parametersCheck');
                    $privatesAccessor->setPrivateProperty($check, 'checkArgumentTypes', true);
                } elseif ($activeRule instanceof InstantiationRule) {
                    /** @var FunctionCallParametersCheck $check */
                    $check = $privatesAccessor->getPrivateProperty($activeRule, 'check');
                    $privatesAccessor->setPrivateProperty($check, 'checkArgumentTypes', true);
                }
            }
        }

        return $activeRules;
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
