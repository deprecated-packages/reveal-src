<?php

declare(strict_types=1);

namespace Reveal\RevealLatte\Tests\Rules\NoNetteRenderUnusedVariableRule;

use Iterator;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Reveal\RevealLatte\Rules\NoNetteRenderUnusedVariableRule;

/**
 * @extends RuleTestCase<NoNetteRenderUnusedVariableRule>
 */
final class NoNetteRenderUnusedVariableRuleTest extends RuleTestCase
{
    /**
     * @dataProvider provideData()
     * @param mixed[] $expectedErrorMessagesWithLines
     */
    public function testRule(string $filePath, array $expectedErrorMessagesWithLines): void
    {
        $this->analyse([$filePath], $expectedErrorMessagesWithLines);
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/RenderWithUnusedVariable.php', [
            [sprintf(NoNetteRenderUnusedVariableRule::ERROR_MESSAGE, 'unused_variable'), 13],
        ]];

        yield [__DIR__ . '/Fixture/SkipVariableInIf.php', []];
        yield [__DIR__ . '/Fixture/SkipIncludeVariable.php', []];
        yield [__DIR__ . '/Fixture/SkipExtendsVariable.php', []];
        yield [__DIR__ . '/Fixture/SkipUsedVariable.php', []];

        yield [__DIR__ . '/Fixture/SkipUsedInInlineMacro.php', []];
        yield [__DIR__ . '/Fixture/SkipFakingOpenCloseMacro.php', []];

        yield [__DIR__ . '/Fixture/SkipUnknownMacro.php', []];
        yield [__DIR__ . '/Fixture/SkipUnionTemplate.php', []];
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
        return self::getContainer()->getByType(NoNetteRenderUnusedVariableRule::class);
    }
}
