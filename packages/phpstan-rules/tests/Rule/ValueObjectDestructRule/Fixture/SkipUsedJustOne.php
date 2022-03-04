<?php

declare(strict_types=1);

namespace Reveal\PHPStanRules\Tests\Rule\ValueObjectDestructRule\Fixture;

use Reveal\PHPStanRules\Tests\Rule\ValueObjectDestructRule\Source\SomeValueObject;

final class SkipUsedJustOne
{
    public function run(SomeValueObject $someValueObject)
    {
        $this->process($someValueObject->getName());
    }

    private function process(string $getName)
    {
    }
}
