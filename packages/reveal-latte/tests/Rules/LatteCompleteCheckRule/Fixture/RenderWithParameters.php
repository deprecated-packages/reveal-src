<?php

declare(strict_types=1);

namespace Reveal\RevealLatte\Tests\Rules\LatteCompleteCheckRule\Fixture;

use Nette\Application\UI\Control;
use Reveal\RevealLatte\Tests\Rules\LatteCompleteCheckRule\Source\ExampleModel;

final class RenderWithParameters extends Control
{
    /**
     * @var ExampleModel[]
     */
    private $listOfObjects = [];

    public function render(): void
    {
        $this->template->render(__DIR__ . '/../Source/ExampleControl.latte', [
            'existingVariable' => '2021-09-11',
            'listOfObjects' => $this->listOfObjects,
        ]);
    }

    protected function createComponentExampleSubControl(): InvalidControlRenderArguments
    {
        return new InvalidControlRenderArguments();
    }
}
