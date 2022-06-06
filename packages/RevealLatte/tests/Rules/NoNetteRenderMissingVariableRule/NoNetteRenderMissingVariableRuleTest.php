<?php

declare(strict_types=1);

namespace Reveal\RevealLatte\Tests\Rules\NoNetteRenderMissingVariableRule;

use Iterator;
use PHPStan\Rules\Rule;
use Reveal\RevealLatte\Rules\NoNetteRenderMissingVariableRule;
use Symplify\PHPStanExtensions\Testing\AbstractServiceAwareRuleTestCase;

/**
 * @extends AbstractServiceAwareRuleTestCase<NoNetteRenderMissingVariableRule>
 */
final class NoNetteRenderMissingVariableRuleTest extends AbstractServiceAwareRuleTestCase
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

    protected function getRule(): Rule
    {
        return $this->getRuleFromConfig(
            NoNetteRenderMissingVariableRule::class,
            __DIR__ . '/config/configured_rule.neon'
        );
    }
}
