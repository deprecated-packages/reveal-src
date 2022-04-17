<?php

declare(strict_types=1);

namespace Reveal\RevealLatte\Tests\Rules\NoUnusedNetteCreateComponentMethodRule\Fixture;

use Nette\Application\UI\Presenter;

final class SkipUsedInArrayDimFetch extends Presenter
{
    protected function createComponentWhatever()
    {
    }

    public function renderDefault()
    {
        return $this['whatever'];
    }
}
