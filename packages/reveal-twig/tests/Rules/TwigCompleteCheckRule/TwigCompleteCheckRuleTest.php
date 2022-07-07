<?php

declare(strict_types=1);

namespace Reveal\RevealTwig\Tests\Rules\TwigCompleteCheckRule;

use Iterator;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Reveal\RevealTwig\Rules\TwigCompleteCheckRule;
use Reveal\RevealTwig\Tests\Rules\TwigCompleteCheckRule\Source\SomeType;

/**
 * @extends \PHPStan\Testing\RuleTestCase<TwigCompleteCheckRule>
 */
final class TwigCompleteCheckRuleTest extends RuleTestCase
{
    /**
     * @dataProvider provideData()
     * @param array<string|int> $expectedErrorMessagesWithLines
     */
    public function testRule(string $filePath, array $expectedErrorMessagesWithLines): void
    {
        $this->markTestSkipped('Use variable names from Twig template are broken');

        $this->analyse([$filePath], $expectedErrorMessagesWithLines);
    }

    public function provideData(): Iterator
    {
        $errorMessage = sprintf('Call to an undefined method %s::nonExistingMethod().', SomeType::class);
        yield [__DIR__ . '/Fixture/SomeMissingVariableController.php', [[$errorMessage, 17]]];

        $firstErrorMessage = sprintf('Call to an undefined method %s::nonExistingMethod().', SomeType::class);
        yield [__DIR__ . '/Fixture/FirstForeachMissing.php', [[$firstErrorMessage, 20]]];

        $secondErrorMessage = sprintf('Call to an undefined method %s::blabla().', SomeType::class);
        yield [__DIR__ . '/Fixture/SomeForeachMissingVariableController.php', [[$secondErrorMessage, 20]]];

        yield [__DIR__ . '/Fixture/SkipExistingMethod.php', []];
        yield [__DIR__ . '/Fixture/SkipExistingProperty.php', []];
        yield [__DIR__ . '/Fixture/SkipExistingArrayAccessItems.php', []];
        yield [__DIR__ . '/Fixture/SkipApp.php', []];
    }

    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/config/configured_rule.neon',
        ];
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(TwigCompleteCheckRule::class);
    }
}
