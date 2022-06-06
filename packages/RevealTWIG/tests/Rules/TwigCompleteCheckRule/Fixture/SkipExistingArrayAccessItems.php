<?php

declare(strict_types=1);

namespace Reveal\RevealTwig\Tests\Rules\TwigCompleteCheckRule\Fixture;

use Reveal\RevealTwig\Tests\Rules\TwigCompleteCheckRule\Source\SomeArrayAccesType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class SkipExistingArrayAccessItems extends AbstractController
{
    public const FILE = __FILE__;

    public function __invoke()
    {
        $someArrayAccess = new SomeArrayAccesType();

        return $this->render(__DIR__ . '/../Source/skip_existing_array_access_items.twig', [
            'some_array_access_type' => $someArrayAccess,
        ]);
    }
}
