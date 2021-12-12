<?php

declare(strict_types=1);

namespace Reveal\RevealNeon\Tests\Rules\AnalyzeNeonRule\Source;

final class ExistingServiceWithConstructor
{
    public function __construct(string $name)
    {
    }
}
