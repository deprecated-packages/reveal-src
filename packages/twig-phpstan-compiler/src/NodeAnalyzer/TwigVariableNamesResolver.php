<?php

declare(strict_types=1);

namespace Reveal\TwigPHPStanCompiler\NodeAnalyzer;

use PhpParser\NodeTraverser;
use Reveal\TemplatePHPStanCompiler\Contract\UsedVariableNamesResolverInterface;
use Reveal\TemplatePHPStanCompiler\NodeVisitor\TwigVariableCollectingNodeVisitor;
use Reveal\TemplatePHPStanCompiler\PhpParser\ParentNodeAwarePhpParser;
use Reveal\TwigPHPStanCompiler\TwigToPhpCompiler;
use Symplify\Astral\Naming\SimpleNameResolver;

final class TwigVariableNamesResolver implements UsedVariableNamesResolverInterface
{
    public function __construct(
        private TwigToPhpCompiler $twigToPhpCompiler,
        private SimpleNameResolver $simpleNameResolver,
        private ParentNodeAwarePhpParser $parentNodeAwarePhpParser
    ) {
    }

    /**
     * @return string[]
     */
    public function resolveFromFilePath(string $filePath): array
    {
        $phpFileContentsWithLineMap = $this->twigToPhpCompiler->compileContent($filePath, []);
        $phpFileContents = $phpFileContentsWithLineMap->getPhpFileContents();

        $stmts = $this->parentNodeAwarePhpParser->parsePhpContent($phpFileContents);

        $variableCollectingNodeVisitor = new TwigVariableCollectingNodeVisitor(
            ['context', 'macros', 'this', '_parent', 'loop', 'tmp'],
            $this->simpleNameResolver,
        );

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($variableCollectingNodeVisitor);
        $nodeTraverser->traverse($stmts);

        return $variableCollectingNodeVisitor->getUsedVariableNames();
    }
}
