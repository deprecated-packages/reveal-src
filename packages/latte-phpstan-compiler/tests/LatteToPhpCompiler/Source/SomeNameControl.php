<?php

declare(strict_types=1);

namespace Symplify\LattePHPStanCompiler\Tests\LatteToPhpCompiler\Source;

use Nette\Application\UI\Control;

final class SomeNameControl extends Control
{
    public function render(string $name): void
    {
    }

    public function handleDoSomething(string $foo, ?array $bar = null): void
    {
    }

    public function handleWithoutParameters(): void
    {
    }
}
