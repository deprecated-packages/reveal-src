<?php

declare(strict_types=1);

namespace Reveal\RevealLatte\Tests\Rules\LatteCompleteCheckRule\Fixture;

use Latte\Engine;
use Reveal\RevealLatte\Tests\Rules\LatteCompleteCheckRule\Source\ExampleModel;

final class RenderUsingEngineWithArrayParams
{
    /**
     * @var ExampleModel[]
     */
    private $listOfObjects = [];

    public function doStuff(): void
    {
        $latte = new Engine();

        $latte->renderToString(__DIR__ . '/../Source/ExampleControl.latte', [
            'existingVariable' => '2021-09-11',
            'listOfObjects' => $this->listOfObjects,
        ]);
    }

    protected function createComponentExampleSubControl(): InvalidControlRenderArguments
    {
        return new InvalidControlRenderArguments();
    }
}
