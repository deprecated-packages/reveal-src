<?php

declare(strict_types=1);

namespace Reveal\RevealLatte\Tests\Rules\NoUnusedNetteCreateComponentMethodRule\Fixture;

use Nette\Application\UI\Presenter;

final class SkipUsedCreateComponentMethod extends Presenter
{
    protected function createComponentSomeComponent()
    {
    }
}
