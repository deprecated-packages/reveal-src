<?php

declare(strict_types=1);

namespace Reveal\RevealNeon\Tests\Generator;

use PHPUnit\Framework\TestCase;
use Reveal\RevealNeon\Generator\Generator;

final class GenerateTest extends TestCase
{
    private Generator $generator;

    protected function setUp(): void
    {
        $this->generator = new Generator();
    }

    public function test(): void
    {
        $errors = $this->generator->fromConfig(__DIR__ . '/Fixture/some_config.neon');

        dump($errors);
    }
}
