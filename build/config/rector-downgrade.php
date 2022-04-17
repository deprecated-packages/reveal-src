<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector;
use Rector\Set\ValueObject\DowngradeLevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->skip([
        // should be skipped - fix in rectordev-main
        DowngradeCovariantReturnTypeRector::class => [
            'tests/Rule/ValueObjectDestructRule/ValueObjectDestructRuleTest.php'
        ],
    ]);

    $rectorConfig->sets([
        DowngradeLevelSetList::DOWN_TO_PHP_72,
    ]);
};
