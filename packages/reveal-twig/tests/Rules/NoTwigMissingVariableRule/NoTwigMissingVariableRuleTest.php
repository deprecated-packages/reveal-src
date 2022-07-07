<?php

declare(strict_types=1);

namespace Reveal\RevealTwig\Tests\Rules\NoTwigMissingVariableRule;

use Iterator;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Reveal\RevealTwig\Rules\NoTwigMissingVariableRule;

/**
 * @extends RuleTestCase<NoTwigMissingVariableRule>
 */
final class NoTwigMissingVariableRuleTest extends RuleTestCase
{
    /**
     * @dataProvider provideData()
     * @param array<string|int> $expectedErrorMessagesWithLines
     */
    public function testRule(string $filePath, array $expectedErrorMessagesWithLines): void
    {
        $this->analyse([$filePath], $expectedErrorMessagesWithLines);
    }

    public function provideData(): Iterator
    {
//        yield [__DIR__ . '/Fixture/SomeMissingVariableController.php', [
//            [sprintf(NoTwigMissingVariableRule::ERROR_MESSAGE, 'missing_variable'), 14],
//        ]];

        yield [__DIR__ . '/Fixture/SkipUsedVariable.php', []];
        yield [__DIR__ . '/Fixture/SkipForeachVariable.php', []];
        yield [__DIR__ . '/Fixture/SkipTemplateSetVariable.php', []];
    }

    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/config/configured_rule.neon'];
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(NoTwigMissingVariableRule::class);
    }
}
