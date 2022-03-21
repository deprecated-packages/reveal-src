<?php

declare(strict_types=1);

namespace Reveal\SymfonyPHPStanRules\Tests\Rule\RequireInvokableControllerRule\Fixture;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

final class SkipInvokableController extends AbstractController
{
    /**
     * @Route()
     */
    public function __invoke()
    {
    }
}
