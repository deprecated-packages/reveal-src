<?php

declare(strict_types=1);

namespace Reveal\RevealLatte\Tests\Rules\NoNetteRenderMissingVariableRule;

use Iterator;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Reveal\RevealLatte\Rules\NoNetteRenderMissingVariableRule;

/**
 * @extends RuleTestCase<NoNetteRenderMissingVariableRule>
 */
final class NoNetteRenderMissingVariableRuleTest extends RuleTestCase
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
        yield [__DIR__ . '/Fixture/RenderWithMissingVariable.php', [
            [sprintf(NoNetteRenderMissingVariableRule::ERROR_MESSAGE, 'use_me'), 13],
        ]];

        yield [__DIR__ . '/Fixture/MultipleMissingVariables.php', [
            [sprintf(NoNetteRenderMissingVariableRule::ERROR_MESSAGE, 'name", "anotherOne'), 13],
        ]];

        yield [__DIR__ . '/Fixture/SkipCompleteVariables.php', []];
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
        return self::getContainer()->getByType(NoNetteRenderMissingVariableRule::class);
    }
}
