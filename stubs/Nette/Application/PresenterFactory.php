<?php

declare(strict_types=1);

namespace Nette\Application;

if (class_exists('Nette\Application\PresenterFactory')) {
    return;
}

class PresenterFactory
{
    public function setMapping(array $mapping)
    {
    }

    public function formatPresenterClass()
    {
    }
}
