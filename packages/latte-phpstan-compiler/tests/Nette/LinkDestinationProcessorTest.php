<?php

declare(strict_types=1);

namespace Reveal\LattePHPStanCompiler\Tests\Nette;

use Iterator;
use PHPStan\Testing\PHPStanTestCase;
use Reveal\LattePHPStanCompiler\Nette\LinkDestinationProcessor;
use Reveal\LattePHPStanCompiler\Tests\LatteToPhpCompiler\Source\Modules\FooModule\FirstFooPresenter;

final class LinkDestinationProcessorTest extends PHPStanTestCase
{
    private LinkDestinationProcessor $linkDestinationProcessor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->linkDestinationProcessor = self::getContainer()->getByType(LinkDestinationProcessor::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(
        string $destination,
        string $actualClassType,
        string $expectedDestination,
    ): void {
        $this->assertSame($expectedDestination, $this->linkDestinationProcessor->process($destination, $actualClassType));
    }

    public function provideData(): Iterator
    {
        $actualClassType = FirstFooPresenter::class;

        yield ['doSomething!', $actualClassType, 'doSomething!'];
        yield ['default', $actualClassType, 'Foo:FirstFoo:default'];
        yield ['FirstFoo:default', $actualClassType, 'Foo:FirstFoo:default'];
        yield ['//FirstFoo:default', $actualClassType, 'Foo:FirstFoo:default'];
        yield [':Foo:FirstFoo:default', $actualClassType, 'Foo:FirstFoo:default'];
        yield ['SecondFoo:default', $actualClassType, 'Foo:SecondFoo:default'];
        yield [':Foo:SecondFoo:default', $actualClassType, 'Foo:SecondFoo:default'];
        yield ['Bar:FirstBar:default', $actualClassType, 'Bar:FirstBar:default'];
        yield ['Bar:SecondBar:default', $actualClassType, 'Bar:SecondBar:default'];
        yield ['//Bar:SecondBar:default', $actualClassType, 'Bar:SecondBar:default'];
    }

    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../../../../packages/template-phpstan-compiler/config/services.neon',
            __DIR__ . '/../../../../packages/latte-phpstan-compiler/config/services.neon',
            __DIR__ . '/../../../../vendor/symplify/astral/config/services.neon',
            __DIR__ . '/link_destination_processor_test.neon',
        ];
    }
}
