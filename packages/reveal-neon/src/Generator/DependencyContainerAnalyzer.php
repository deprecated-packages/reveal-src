<?php

declare(strict_types=1);

namespace Reveal\RevealNeon\Generator;

use Nette\Configurator;
use Nette\DI\ServiceCreationException;
use Nette\Utils\Strings;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @see \Reveal\RevealNeon\Tests\Generator\DependencyContainerAnalyzerTest
 */
final class DependencyContainerAnalyzer
{
    public function analyseConfig(string $servicesFilePath): array
    {
        $configurator = new Configurator();
        $configurator->setTempDirectory(__DIR__ . '/local-temp');
        $configurator->addConfig($servicesFilePath);

        // @todo propagate non-existing class to phpstan error to unite
        // @todo always clear cache here

        try {
            $configurator->createContainer();
        } catch (ServiceCreationException $serviceCreationException) {
            $match = Strings::match($serviceCreationException->getMessage(), "#Class '(?<class_name>.*?)' not found#");
            if ($match !== null) {
                $className = $match['class_name'];

                $errorMessage = sprintf('Class "%s" was not found', $className);
                $ruleError = RuleErrorBuilder::message($errorMessage)
                    // @todo add line
                    ->file($servicesFilePath)
                    ->build();

                return [$ruleError];
            }

            throw $serviceCreationException;
        }

        // @todo analyse errors
        dump('___');

        return [];
    }
}
