<?php

declare(strict_types=1);

namespace Reveal\PHPStanRules\Tests\Rule\ValueObjectDestructRule;

use Iterator;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Reveal\PHPStanRules\Rule\ValueObjectDestructRule;
use Reveal\PHPStanRules\Tests\TestRuleFactory;

/**
 * @extends RuleTestCase<ValueObjectDestructRule>
 */
final class ValueObjectDestructRuleTest extends RuleTestCase
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
        yield [__DIR__ . '/Fixture/UsingPublicMethods.php', [[ValueObjectDestructRule::ERROR_MESSAGE, 13]]];

        yield [__DIR__ . '/Fixture/SkipUsedJustOne.php', []];
        yield [__DIR__ . '/Fixture/SkipSingleMethod.php', []];
        yield [__DIR__ . '/Fixture/SkipSingleMethodCalledTwice.php', []];
    }

    protected function getRule(): Rule
    {
        $testRuleFactory = new TestRuleFactory();
        return $testRuleFactory->getServiceFromConfig(__DIR__ . '/config/configured_rule.neon', ValueObjectDestructRule::class);
    }
}
