<?php
declare(strict_types=1);

namespace Reveal\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class AnalyzeTwigCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('analyze-twig');
        $this->setDescription('Analyze TWIG files that all constants exist etc.');
        $this->addArgument('path');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // @todo compile file to PHP
        // run PHPStan on it :)


        dump($input->getArgument('path'));
        die;
    }
}
