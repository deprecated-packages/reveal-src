<?php

declare(strict_types=1);

namespace Reveal\TwigPHPStanCompiler;

use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;
use Reveal\TemplatePHPStanCompiler\NodeFactory\VarDocNodeFactory;
use Reveal\TemplatePHPStanCompiler\ValueObject\VariableAndType;
use Reveal\TwigPHPStanCompiler\PhpParser\NodeVisitor\AppendExtractedVarTypesNodeVisitor;
use Symplify\Astral\Naming\SimpleNameResolver;
use Symplify\Astral\PhpParser\SmartPhpParser;

final class TwigVarTypeDocBlockDecorator
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
    public function decorateTwigContentWithTypes(string $phpContent, array $variablesAndTypes): string
    {
        // convert to "@var types $variable"
        $phpNodes = $this->smartPhpParser->parseString($phpContent);

        $nodeTraverser = new NodeTraverser();
        $appendExtractedVarTypesNodeVisitor = new AppendExtractedVarTypesNodeVisitor(
            $this->simpleNameResolver,
            $this->varDocNodeFactory,
            $variablesAndTypes
        );

        $nodeTraverser->addVisitor($appendExtractedVarTypesNodeVisitor);
        $nodeTraverser->traverse($phpNodes);

        $printedPhpContent = $this->printerStandard->prettyPrintFile($phpNodes);
        return rtrim($printedPhpContent) . PHP_EOL;
    }
}
