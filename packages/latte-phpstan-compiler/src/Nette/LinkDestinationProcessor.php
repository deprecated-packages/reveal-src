<?php

declare(strict_types=1);

namespace Reveal\LattePHPStanCompiler\Nette;

/**
 * change link destination to full format (module (if exists) + presenter + action)
 * if actual presenter is Foo:FirstFoo
 *
 * `default` is changed to `Foo:FirstFoo:default`
 * `FirstFoo:default` is changed to `Foo:FirstFoo:default`
 * `SecondFoo:default` is changed to `Foo:SecondFoo:default`
 * `Bar:SecondBar:default` stays as is because destination is full
 */
final class LinkDestinationProcessor
{
    public function __construct(
        private PresenterFactoryFaker $presenterFactoryFaker,
    ) {
    }

    public function process(string $destination, ?string $actualClassType = null): string
    {
        $destination = ltrim($destination, '/:');

        if (str_ends_with($destination, '!')) {
            return $destination;
        }

        $destinationParts = explode(':', $destination);
        if (count($destinationParts) === 3) {
            return $destination;
        }

        if ($actualClassType === null) {
            return $destination;
        }

        $presenterFactory = $this->presenterFactoryFaker->getPresenterFactory();
        $presenterName = @$presenterFactory->unformatPresenterClass($actualClassType);

        if ($presenterName === null) {
            return $destination;
        }

        if (count($destinationParts) === 1) {
            return $presenterName . ':' . $destination;
        }

        $presenterNameParts = explode(':', $presenterName, 2);

        $module = isset($presenterNameParts[1]) ? $presenterNameParts[0] : null;
        if ($module) {
            return $module . ':' . $destination;
        }

        return $destination;
    }
}
