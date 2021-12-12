<?php

declare(strict_types=1);

namespace Reveal\RevealNeon\Tests\Rules\AnalyzeNeonRule;

use PHPStan\Rules\Rule;
use Reveal\RevealNeon\Rules\AnalyzeNeonRule;
use Symplify\PHPStanExtensions\Testing\AbstractServiceAwareRuleTestCase;
use Symplify\PHPStanRules\Rules\Enum\RequireConstantInMethodCallPositionRule;

/**
 * @extends AbstractServiceAwareRuleTestCase<AnalyzeNeonRule>
 */
final class AnalyzeNeonRuleTest extends AbstractServiceAwareRuleTestCase
{
    /**
     * @dataProvider provideData()
     * @param array<string|int> $expectedErrorMessagesWithLines
     */
    public function testRule(string $filePath, array $expectedErrorMessagesWithLines): void
    {
        $this->analyse([$filePath], $expectedErrorMessagesWithLines);
    }

    public function provideData(): \Iterator
    {
        $errorMessage = sprintf(RequireConstantInMethodCallPositionRule::ERROR_MESSAGE, 0);
        yield [__DIR__ . '/Fixture/SomeMethodCallWithoutConstant.php', [[$errorMessage, 14]]];
    }

    protected function getRule(): Rule
    {
        return $this->getRuleFromConfig(AnalyzeNeonRule::class, __DIR__ . '/config/configured_rule.neon');
    }
}
