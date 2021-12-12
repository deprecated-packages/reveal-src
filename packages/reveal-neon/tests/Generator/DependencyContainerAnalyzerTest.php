<?php

declare(strict_types=1);

namespace Reveal\RevealNeon\Tests\Generator;

use Iterator;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPUnit\Framework\TestCase;
use Reveal\RevealNeon\Generator\DependencyContainerAnalyzer;
use Webmozart\Assert\Assert;

final class DependencyContainerAnalyzerTest extends TestCase
{
    private DependencyContainerAnalyzer $dependencyContainerAnalyzer;

    protected function setUp(): void
    {
        $this->dependencyContainerAnalyzer = new DependencyContainerAnalyzer();
    }

    /**
     * @dataProvider provideData()
     * @param RuleError[] $expectedRuleErrors
     */
    public function test(string $configFilepath, array $expectedRuleErrors): void
    {
        $ruleErrors = $this->dependencyContainerAnalyzer->analyseConfig($configFilepath);
        $this->assertEquals($expectedRuleErrors, $ruleErrors);
    }

    public function provideData(): Iterator
    {
        $filePath = realpath(__DIR__ . '/../Generator/Fixture/missing_class.neon');
        Assert::fileExists($filePath);

        $ruleError = RuleErrorBuilder::message('Class "NotHere" was not found')
            ->file($filePath)
            ->build();

        yield [__DIR__ . '/Fixture/missing_class.neon', [$ruleError]];

        // @todo
        // yield [__DIR__ . '/Fixture/invalid_constructor_argument_type.neon', []];
    }
}
