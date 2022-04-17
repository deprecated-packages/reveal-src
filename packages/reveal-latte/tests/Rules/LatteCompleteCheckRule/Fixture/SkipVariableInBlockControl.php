<?php

declare(strict_types=1);

namespace Reveal\RevealLatte\Tests\Rules\LatteCompleteCheckRule\Fixture;

use Nette\Application\UI\Control;

final class SkipVariableInBlockControl extends Control
{
    public function render()
    {
        $this->template->render(__DIR__ . '/../Source/variable_in_block.latte', [
            'hello' => 'world',
        ]);
    }
}
