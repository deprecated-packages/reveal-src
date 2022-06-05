<?php
declare(strict_types=1);

namespace Reveal\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

final class ApplicationFactory
{
    /**
     * @param Command[] $commands
     */
    public function __construct(private array $commands)
    {
    }

    public function create(): Application
    {
        $application = new Application();
        $application->addCommands($this->commands);

        return $application;
    }
}
