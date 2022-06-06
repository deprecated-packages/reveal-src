<?php

declare(strict_types=1);

namespace Reveal\TemplatePHPStanCompiler;

use Nette\Utils\Strings;
use PHPStan\Analyser\Error;

/**
 * @api
 * @see \Reveal\TemplatePHPStanCompiler\Tests\ErrorSkipperTest
 */
final class ErrorSkipper
{
    /**
     * @param Error[] $errors
     * @param string[] $errorIgnores
     * @return Error[]
     */
    public function skipErrors(array $errors, array $errorIgnores): array
    {
        $filteredErrors = [];

        foreach ($errors as $error) {
            foreach ($errorIgnores as $errorIgnore) {
                if (Strings::match($error->getMessage(), $errorIgnore)) {
                    continue 2;
                }
            }

            $filteredErrors[] = $error;
        }

        return $filteredErrors;
    }
}
