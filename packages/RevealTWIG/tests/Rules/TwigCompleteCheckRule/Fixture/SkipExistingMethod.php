<?php

declare(strict_types=1);

namespace Reveal\RevealTwig\Tests\Rules\TwigCompleteCheckRule\Fixture;

use Reveal\RevealTwig\Tests\Rules\TwigCompleteCheckRule\Source\SomeType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class SkipExistingMethod extends AbstractController
{
    public function __invoke()
    {
        return $this->render(__DIR__ . '/../Source/skip_existing_method.twig', [
            'some_type' => new SomeType(),
        ]);
    }
}
