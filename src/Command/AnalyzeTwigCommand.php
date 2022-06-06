<?php

declare(strict_types=1);

namespace Reveal\Command;

use Reveal\Enum\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;
use Webmozart\Assert\Assert;

final class AnalyzeTwigCommand extends Command
{
    public function __construct(
        private readonly SymfonyStyle $symfonyStyle
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('analyze-twig');
        $this->setDescription('Analyze TWIG files for missing constants etc.');
        $this->addArgument(Option::PATHS, InputArgument::IS_ARRAY, 'Path to TWIG files or directories');
        $this->addOption(Option::AUTOLOAD_FILE, null, InputOption::VALUE_REQUIRED, 'The vendor/autoload.php file of your project');
        $this->addOption(Option::TWIG_PROVIDER, null, InputOption::VALUE_REQUIRED, 'Path to PHP file that return your instance of TWIG');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // @todo compile file to PHP
        // run PHPStan on it :)

        $paths = $input->getArgument(Option::PATHS);
        Assert::isArray($paths);
        Assert::allString($paths);
        Assert::allFileExists($paths);

        $autoloadFile = $input->getOption(Option::AUTOLOAD_FILE);
        if ($autoloadFile) {
            Assert::string($autoloadFile);
            Assert::fileExists($autoloadFile);

            include_once $autoloadFile;
            $message = sprintf('Autoloaded project file "%s"', $autoloadFile);
            $this->symfonyStyle->success($message);
        }

        // 2. load twig engine from the particular project
        $twigProvider = $input->getOption(Option::TWIG_PROVIDER);
        Assert::string($autoloadFile);
        Assert::fileExists($autoloadFile);

        // @todo
        // here we need to scope this project, to avoid container crash like
        // Call to undefined method Symfony\Component\DependencyInjection\ContainerBuilder::addScope()
        $twigEngine = require_once $twigProvider;

        /** @var Environment $twigEngine */
        $twigEngine->render('');

        foreach ($paths as $path) {
            if (is_file($path)) {
                // parse to PHP version
                dump($path);
            }
        }

        // not ready yet
        return self::FAILURE;
    }
}
