<?php

declare(strict_types=1);

namespace Reveal\Tests\LatteToPhpCompiler\Source;

final class StaticFilter
{
    public static function process(string $var): string
    {
        return $var;
    }
}
