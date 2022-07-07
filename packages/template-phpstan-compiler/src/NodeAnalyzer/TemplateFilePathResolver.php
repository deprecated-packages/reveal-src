<?php

declare(strict_types=1);

namespace Reveal\TemplatePHPStanCompiler\NodeAnalyzer;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use Symfony\Component\Finder\Finder;
use Symplify\Astral\NodeValue\NodeValueResolver;

/**
 * @api
 */
final class TemplateFilePathResolver
{
    public function __construct(
        private NodeValueResolver $nodeValueResolver
    ) {
    }

    /**
     * @return string[]
     */
    public function resolveExistingFilePaths(Expr $expr, Scope $scope, string $templateSuffix): array
    {
        $resolvedValue = $this->nodeValueResolver->resolve($expr, $scope->getFile());

        $possibleTemplateFilePaths = $this->arrayizeStrings($resolvedValue);
        if ($possibleTemplateFilePaths === []) {
            return [];
        }

        $resolvedTemplateFilePaths = [];

        foreach ($possibleTemplateFilePaths as $possibleTemplateFilePath) {
            // file could not be found, nothing we can do
            if (! is_string($possibleTemplateFilePath)) {
                continue;
            }

            // 1. file exists
            if (file_exists($possibleTemplateFilePath)) {
                $resolvedTemplateFilePaths[] = $possibleTemplateFilePath;
                continue;
            }

            // 2. look for possible template candidate in /templates directory
            $filePath = $this->findCandidateInTemplatesDirectory($possibleTemplateFilePath, $templateSuffix);
            if ($filePath === null) {
                continue;
            }

            $fileRealPath = realpath($filePath);
            if ($fileRealPath === false) {
                continue;
            }

            $resolvedTemplateFilePaths[] = $fileRealPath;
        }

        return $resolvedTemplateFilePaths;
    }

    /**
     * Helps with mapping of short name to FQN template name; Make configurable via rule constructor?
     */
    private function findCandidateInTemplatesDirectory(
        string $resolvedTemplateFilePath,
        string $templateSuffix
    ): string|null {
        $symfonyTemplatesDirectory = getcwd() . '/templates';
        if (! file_exists($symfonyTemplatesDirectory)) {
            return null;
        }

        $finder = new Finder();
        $finder->in($symfonyTemplatesDirectory)
            ->files()
            ->name('*.' . $templateSuffix);

        foreach ($finder->getIterator() as $fileInfo) {
            if (str_ends_with($fileInfo->getRealPath(), $resolvedTemplateFilePath)) {
                return $fileInfo->getRealPath();
            }
        }

        return null;
    }

    /**
     * @return string[]|mixed[]
     */
    private function arrayizeStrings(mixed $resolvedValue): array
    {
        if (is_string($resolvedValue)) {
            return [$resolvedValue];
        }

        if (is_array($resolvedValue)) {
            return $resolvedValue;
        }

        // impossible to resolve
        return [];
    }
}
