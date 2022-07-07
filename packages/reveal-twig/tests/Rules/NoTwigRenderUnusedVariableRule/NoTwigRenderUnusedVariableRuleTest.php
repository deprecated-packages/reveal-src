<?php

declare(strict_types=1);

namespace Reveal\RevealTwig\Tests\Rules\NoTwigRenderUnusedVariableRule;

use Iterator;
use PHPStan\Rules\Rule;
use Reveal\RevealTwig\Rules\NoTwigRenderUnusedVariableRule;

/**
 * @extends \PHPStan\Testing\RuleTestCase<NoTwigRenderUnusedVariableRule>
 */
final class NoTwigRenderUnusedVariableRuleTest extends \PHPStan\Testing\RuleTestCase
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
        yield [__DIR__ . '/Fixture/RenderWithUnusedVariable.php', [
            [sprintf(NoTwigRenderUnusedVariableRule::ERROR_MESSAGE, 'unused_variable'), 14],
        ]];

        yield [__DIR__ . '/Fixture/RenderBareTwigWithUnusedVariable.php', [
            [sprintf(NoTwigRenderUnusedVariableRule::ERROR_MESSAGE, 'unused_variable'), 13],
        ]];

        yield [__DIR__ . '/Fixture/SkipUsedVariable.php', []];
        yield [__DIR__ . '/Fixture/SkipUnionSingleUsed.php', []];
        yield [__DIR__ . '/Fixture/SkipForeachUsedVariable.php', []];
        yield [__DIR__ . '/Fixture/SkipIncludeArray.php', []];
        yield [__DIR__ . '/Fixture/SkipControllerRouter.php', []];

        yield [__DIR__ . '/Fixture/RenderTwoTemplates.php', [
            [sprintf(NoTwigRenderUnusedVariableRule::ERROR_MESSAGE, 'unused_variable'), 16],
        ]];
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
        return self::getContainer()->getByType(NoTwigRenderUnusedVariableRule::class);
    }
}
