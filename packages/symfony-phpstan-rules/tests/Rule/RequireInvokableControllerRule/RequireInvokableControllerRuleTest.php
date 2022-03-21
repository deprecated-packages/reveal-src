<?php

declare(strict_types=1);

namespace Reveal\SymfonyPHPStanRules\Tests\Rule\RequireInvokableControllerRule;

use Iterator;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Reveal\SymfonyPHPStanRules\Rule\RequireInvokableControllerRule;
use Reveal\SymfonyPHPStanRules\Tests\TestRuleFactory;

/**
 * @extends RuleTestCase<RequireInvokableControllerRule>
 */
final class RequireInvokableControllerRuleTest extends RuleTestCase
{
    /**
     * @dataProvider provideData()
     * @param mixed[] $expectedErrorsWithLines
     */
    public function testRule(string $filePath, array $expectedErrorsWithLines): void
    {
        $this->analyse([$filePath], $expectedErrorsWithLines);
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/MissnamedController.php', [[RequireInvokableControllerRule::ERROR_MESSAGE, 15]]];
        yield [__DIR__ . '/Fixture/MissnamedRouteAttributeController.php', [[RequireInvokableControllerRule::ERROR_MESSAGE, 12]]];

        yield [__DIR__ . '/Fixture/SkipInvokableController.php', []];
    }

    protected function getRule(): Rule
    {
        $testRuleFactory = new TestRuleFactory();
        return $testRuleFactory->getServiceFromConfig(__DIR__ . '/config/configured_rule.neon', RequireInvokableControllerRule::class);
    }
}
