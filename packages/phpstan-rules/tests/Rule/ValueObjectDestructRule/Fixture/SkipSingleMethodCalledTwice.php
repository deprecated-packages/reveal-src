<?php

declare(strict_types=1);

namespace Reveal\PHPStanRules\Tests\Rule\ValueObjectDestructRule\Fixture;

use Reveal\PHPStanRules\Tests\Rule\ValueObjectDestructRule\Source\PossiblyService;

final class SkipSingleMethodCalledTwice
{
    public function run(PossiblyService $possiblyService)
    {
        $this->process($possiblyService->run(), $possiblyService->run());
    }

    private function process(int $number, int $anotherNumber)
    {
    }
}
