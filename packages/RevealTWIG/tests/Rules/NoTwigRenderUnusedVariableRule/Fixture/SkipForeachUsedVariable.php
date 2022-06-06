<?php

declare(strict_types=1);

namespace Reveal\RevealTwig\Tests\Rules\NoTwigRenderUnusedVariableRule\Fixture;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class SkipForeachUsedVariable extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render(__DIR__ . '/../Source/template_with_foreach.twig', [
            'items' => [1, 2, 3],
        ]);
    }
}
