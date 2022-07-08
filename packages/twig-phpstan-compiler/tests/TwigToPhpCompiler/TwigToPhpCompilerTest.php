<?php

declare(strict_types=1);

namespace Reveal\TwigPHPStanCompiler\Tests\TwigToPhpCompiler;

use Iterator;
use PHPStan\Testing\PHPStanTestCase;
use PHPStan\Type\StringType;
use Reveal\TemplatePHPStanCompiler\ValueObject\VariableAndType;
use Reveal\TwigPHPStanCompiler\TwigToPhpCompiler;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\DataProvider\StaticFixtureUpdater;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TwigToPhpCompilerTest extends PHPStanTestCase
{
    private TwigToPhpCompiler $twigToPhpCompiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->twigToPhpCompiler = self::getContainer()->getByType(TwigToPhpCompiler::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $inputFileInfoAndExpected = StaticFixtureSplitter::splitFileInfoToLocalInputAndExpected($fileInfo);

        $phpFileContentsWithLineMap = $this->twigToPhpCompiler->compileContent(
            $inputFileInfoAndExpected->getInputFileRealPath(),
            []
        );
        $phpFileContent = $phpFileContentsWithLineMap->getPhpFileContents();

        // update test fixture if the content has changed
        StaticFixtureUpdater::updateFixtureContent(
            $inputFileInfoAndExpected->getInputFileContent(),
            $phpFileContent,
            $fileInfo
        );

        $expectedFileContent = (string) $inputFileInfoAndExpected->getExpected();
        $this->assertStringMatchesFormat($expectedFileContent, $phpFileContent);
    }

    public function testTypes(): void
    {
        $variablesAndTypes = [new VariableAndType('value', new StringType())];

        $phpFileContentsWithLineMap = $this->twigToPhpCompiler->compileContent(
            __DIR__ . '/FixtureWithTypes/input_file.twig',
            $variablesAndTypes
        );
        $phpFileContent = $phpFileContentsWithLineMap->getPhpFileContents();

        $this->assertStringMatchesFormatFile(__DIR__ . '/FixtureWithTypes/expected_compiled.php', $phpFileContent);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return StaticFixtureFinder::yieldDirectoryExclusively(__DIR__ . '/Fixture', '*.twig');
    }

    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../../../../packages/twig-phpstan-compiler/config/services.neon',
            __DIR__ . '/../../../../vendor/symplify/astral/config/services.neon',
            __DIR__ . '/../../../../packages/template-phpstan-compiler/config/services.neon',
        ];
    }
}
