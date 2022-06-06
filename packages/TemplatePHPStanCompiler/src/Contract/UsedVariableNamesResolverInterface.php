<?php

declare(strict_types=1);

namespace Reveal\TemplatePHPStanCompiler\Contract;

/**
 * @api
 */
interface UsedVariableNamesResolverInterface
{
    /**
     * @return string[]
     */
    public function resolveFromFilePath(string $filePath): array;
}
