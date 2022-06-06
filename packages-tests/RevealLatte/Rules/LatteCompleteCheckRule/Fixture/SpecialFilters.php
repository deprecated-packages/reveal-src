<?php

declare(strict_types=1);

namespace Reveal\RevealLatte\Tests\Rules\LatteCompleteCheckRule\Fixture;

use Nette\Application\UI\Control;

final class SpecialFilters extends Control
{
    public function render(): void
    {
        $this->template->someString = 'foo bar';
        $this->template->setFile(__DIR__ . '/../Source/SpecialFilters.latte');
        $this->template->render();
    }
}
