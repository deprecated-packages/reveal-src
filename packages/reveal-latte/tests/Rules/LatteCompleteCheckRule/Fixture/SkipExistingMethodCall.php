<?php

declare(strict_types=1);

namespace Reveal\RevealLatte\Tests\Rules\LatteCompleteCheckRule\Fixture;

use Nette\Application\UI\Control;
use Reveal\RevealLatte\Tests\Rules\LatteCompleteCheckRule\Source\SomeTypeWithMethods;

final class SkipExistingMethodCall extends Control
{
    public function render()
    {
        $someType = new SomeTypeWithMethods();

        $this->template->render(__DIR__ . '/../Source/existing_method_call.latte', [
            'someType' => $someType,
        ]);
    }
}
