<?php

declare(strict_types=1);

namespace Reveal\PHPStanRules\Tests\Rule\ValueObjectDestructRule;

use Iterator;
use PHPStan\Rules\Rule;
use Reveal\PHPStanRules\Rule\ValueObjectDestructRule;
use Symplify\PHPStanExtensions\Testing\AbstractServiceAwareRuleTestCase;

/**
 * @extends AbstractServiceAwareRuleTestCase<ValueObjectDestructRule>
 */
final class ValueObjectDestructRuleTest extends AbstractServiceAwareRuleTestCase
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
    }

    protected function getRule(): Rule
    {
        return $this->getRuleFromConfig(
            ValueObjectDestructRule::class,
            __DIR__ . '/config/configured_rule.neon'
        );
    }
}
