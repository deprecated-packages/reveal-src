<?php

declare(strict_types=1);

namespace Reveal\LattePHPStanCompiler;

use Exception\LattePHPStanCompilerException;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;
use Reveal\TemplatePHPStanCompiler\NodeFactory\VarDocNodeFactory;
use Reveal\TemplatePHPStanCompiler\ValueObject\VariableAndType;
use Symplify\Astral\Naming\SimpleNameResolver;
use Symplify\Astral\PhpParser\SmartPhpParser;

final class LatteVarTypeDocBlockDecorator
{
    public function __construct(
        private SmartPhpParser $smartPhpParser,
        private Standard $printerStandard,
        private SimpleNameResolver $simpleNameResolver,
        private VarDocNodeFactory $varDocNodeFactory,
    ) {
    }

    /**
     * @param VariableAndType[] $variablesAndTypes
     */
    public function decorateLatteContentWithTypes(string $phpContent, array $variablesAndTypes): string
    {
        // convert to "@var types $variable"
        $phpStmts = $this->smartPhpParser->parseString($phpContent);
        if ($phpStmts === []) {
            throw new LattePHPStanCompilerException();
        }

        $nodeTraverser = new NodeTraverser();
        $appendExtractedVarTypesNodeVisitor = new \Reveal\LattePHPStanCompiler\PhpParser\NodeVisitor\AppendExtractedVarTypesNodeVisitor(
            $this->simpleNameResolver,
            $this->varDocNodeFactory,
            $variablesAndTypes
        );

        $nodeTraverser->addVisitor($appendExtractedVarTypesNodeVisitor);
        $nodeTraverser->traverse($phpStmts);

        $printedPhpContent = $this->printerStandard->prettyPrintFile($phpStmts);

        return rtrim($printedPhpContent) . PHP_EOL;
    }
}
