<?php

declare(strict_types=1);

namespace Symplify\PHPStanLatteRules\Tests\Rules\NoUnusedNetteCreateComponentMethodRule\Fixture;

use Nette\Application\UI\Presenter;

final class UnusedCreateComponentMethod extends Presenter
{
    protected function createComponentWhatever()
    {
    }
}
