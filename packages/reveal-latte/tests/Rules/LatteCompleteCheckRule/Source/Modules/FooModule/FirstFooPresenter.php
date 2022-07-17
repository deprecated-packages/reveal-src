<?php

declare(strict_types=1);

namespace Reveal\RevealLatte\Tests\Rules\LatteCompleteCheckRule\Source\Modules\FooModule;

class FirstFooPresenter
{
    public function renderDefault(int $limit, ?array $add = null): void
    {
    }

    public function actionGrid(int $limit, ?array $add = null): void
    {
    }

    public function renderGrid(int $limit, ?array $add = null): void
    {
    }

    public function handleDoSomething(string $foo): void
    {
    }
}
