<?php

declare(strict_types=1);

namespace Reveal\RevealTwig\Tests\Rules\TwigCompleteCheckRule\Fixture;

use Reveal\RevealTwig\Tests\Rules\TwigCompleteCheckRule\Source\SomeType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class SomeForeachMissingVariableController extends AbstractController
{
    public function __invoke(): Response
    {
        $templateFilePath = __DIR__ . '/../Source/non_existing_method_foreach.twig';

        $someVariable = new SomeType();
        $someTypes = [$someVariable];

        return $this->render($templateFilePath, [
            'some_types' => $someTypes,
        ]);
    }
}
