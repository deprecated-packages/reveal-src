<?php

declare(strict_types=1);

use Reveal\Kernel\RevealKernel;
use Symplify\SymplifyKernel\ValueObject\KernelBootAndApplicationRun;

require __DIR__ . '/../vendor/autoload.php';


$kernelBootAndApplicationRun = new KernelBootAndApplicationRun(RevealKernel::class);
$kernelBootAndApplicationRun->run();
