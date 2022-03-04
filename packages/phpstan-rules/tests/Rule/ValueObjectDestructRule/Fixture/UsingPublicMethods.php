<?php

declare(strict_types=1);

namespace Reveal\PHPStanRules\Tests\Rule\ValueObjectDestructRule\Fixture;

use Reveal\PHPStanRules\Tests\Rule\ValueObjectDestructRule\Source\SomeValueObject;

final class UsingPublicMethods
{
    public function run(SomeValueObject $someValueObject)
    {
        $this->process($someValueObject->getName(), $someValueObject->getSurname());
    }

    private function process(string $getName, string $getSurname)
    {
    }
}
