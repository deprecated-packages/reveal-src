<?php

declare(strict_types=1);

namespace Reveal\RevealLatte\Tests\Rules\LatteCompleteCheckRule\Fixture;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Reveal\RevealLatte\Tests\Rules\LatteCompleteCheckRule\Source\ExampleModel;

final class ControlWithForm extends Control
{
    /**
     * @var ExampleModel[]
     */
    private $listOfObjects = [];

    public function render(): void
    {
        $this->template->existingVariable = '2021-09-11';
        $this->template->listOfObjects = $this->listOfObjects;
        $this->template->setFile(__DIR__ . '/../Source/ExampleControl.latte');
        $this->template->render();
    }

    protected function createComponentExampleSubControl(): Form
    {
        return new Form();
    }
}
