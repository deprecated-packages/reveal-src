<?php

declare(strict_types=1);

use Reveal\Console\ApplicationFactory;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->public()
        ->autoconfigure()
        ->autowire();

    $services->load('Reveal\\', __DIR__ . '/../src')
        ->exclude(__DIR__ . '/../src/Kernel');

    $services->set(Application::class)
        ->factory([service(ApplicationFactory::class), 'create']);
};
