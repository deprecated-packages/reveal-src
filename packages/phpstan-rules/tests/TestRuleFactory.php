<?php

declare(strict_types=1);

namespace Reveal\PHPStanRules\Tests;

use PHPStan\DependencyInjection\ContainerFactory;
use PHPStan\Rules\Rule;

final class TestRuleFactory
{
    /**
     * @template TRule as Rule
     * @param class-string<TRule> $ruleClass
     * @return TRule
     */
    public function getServiceFromConfig(string $config, string $ruleClass): Rule
    {
        $containerFactory = new ContainerFactory(getcwd());
        $tempDirectory = sys_get_temp_dir() . '/reveal_phpstan_tests/id_' . random_int(0, 1000);

        $container = $containerFactory->create($tempDirectory, [$config], [], []);
        return $container->getByType($ruleClass);
    }
}
