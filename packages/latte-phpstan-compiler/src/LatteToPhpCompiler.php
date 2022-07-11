<?php

declare(strict_types=1);

namespace Reveal\LattePHPStanCompiler;

use Latte\Parser;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Reveal\LattePHPStanCompiler\Contract\LatteToPhpCompilerNodeVisitorInterface;
use Reveal\LattePHPStanCompiler\Latte\LineCommentCorrector;
use Reveal\LattePHPStanCompiler\Latte\UnknownMacroAwareLatteCompiler;
use Reveal\LattePHPStanCompiler\PhpParser\NodeVisitor\ControlRenderToExplicitCallNodeVisitor;
use Reveal\LattePHPStanCompiler\PhpParser\NodeVisitor\LinkNodeVisitor;
use Reveal\LattePHPStanCompiler\ValueObject\ComponentNameAndType;
use Reveal\TemplatePHPStanCompiler\ValueObject\VariableAndType;
use Symplify\Astral\Naming\SimpleNameResolver;
use Symplify\PHPStanRules\Exception\ShouldNotHappenException;
use Symplify\SmartFileSystem\SmartFileSystem;

/**
 * @see \Reveal\LattePHPStanCompiler\Tests\LatteToPhpCompiler\LatteToPhpCompilerTest
 */
final class LatteToPhpCompiler
{
    /**
     * @param LatteToPhpCompilerNodeVisitorInterface[] $nodeVisitors
     */
    public function __construct(
        private SmartFileSystem $smartFileSystem,
        private Parser $latteParser,
        private UnknownMacroAwareLatteCompiler $unknownMacroAwareLatteCompiler,
        private SimpleNameResolver $simpleNameResolver,
        private Standard $printerStandard,
        private LineCommentCorrector $lineCommentCorrector,
        private LatteVarTypeDocBlockDecorator $latteVarTypeDocBlockDecorator,
        private array $nodeVisitors,
    ) {
    }

    /**
     * @param VariableAndType[] $variablesAndTypes
     * @param ComponentNameAndType[] $componentNamesAndtTypes
     */
    public function compileContent(
        string $templateFileContent,
        array $variablesAndTypes,
        array $componentNamesAndtTypes
    ): string {
        $this->ensureIsNotFilePath($templateFileContent);

        $latteTokens = $this->latteParser->parse($templateFileContent);

        $rawPhpContent = $this->unknownMacroAwareLatteCompiler->compile($latteTokens, 'DummyTemplateClass');
        $rawPhpContent = $this->lineCommentCorrector->correctLineNumberPosition($rawPhpContent);

        $phpStmts = $this->parsePhpContentToPhpStmts($rawPhpContent);

        $this->decorateStmts($phpStmts, $variablesAndTypes, $componentNamesAndtTypes);
        $phpContent = $this->printerStandard->prettyPrintFile($phpStmts);

        return $this->latteVarTypeDocBlockDecorator->decorateLatteContentWithTypes($phpContent, $variablesAndTypes);
    }

    /**
     * @param VariableAndType[] $variablesAndTypes
     * @param ComponentNameAndType[] $componentNamesAndTypes
     */
    public function compileFilePath(
        string $templateFilePath,
        array $variablesAndTypes,
        array $componentNamesAndTypes
    ): string {
        $templateFileContent = $this->smartFileSystem->readFile($templateFilePath);
        return $this->compileContent($templateFileContent, $variablesAndTypes, $componentNamesAndTypes);
    }

    /**
     * @return Stmt[]
     */
    private function parsePhpContentToPhpStmts(string $rawPhpContent): array
    {
        $parserFactory = new ParserFactory();

        $phpParser = $parserFactory->create(ParserFactory::PREFER_PHP7);
        return (array) $phpParser->parse($rawPhpContent);
    }

    /**
     * @param Stmt[] $phpStmts
     * @param VariableAndType[] $variablesAndTypes
     * @param ComponentNameAndType[] $componentNamesAndTypes
     */
    private function decorateStmts(array $phpStmts, array $variablesAndTypes, array $componentNamesAndTypes): void
    {
        $nodeTraverser = new NodeTraverser();

        $controlRenderToExplicitCallNodeVisitor = new ControlRenderToExplicitCallNodeVisitor(
            $this->simpleNameResolver,
            $componentNamesAndTypes
        );
        $nodeTraverser->addVisitor($controlRenderToExplicitCallNodeVisitor);

        foreach ($this->nodeVisitors as $nodeVisitor) {
            if ($nodeVisitor instanceof LinkNodeVisitor) {
                $nodeVisitor->setVariablesAndTypes($variablesAndTypes);
            }
            $nodeTraverser->addVisitor($nodeVisitor);
        }

        $nodeTraverser->traverse($phpStmts);
    }

    private function ensureIsNotFilePath(string $templateFileContent): void
    {
        if (! file_exists($templateFileContent)) {
            return;
        }

        $errorMessage = sprintf(
            'The file path "%s" was passed as 1st argument in "%s()" metohd. Must be file content instead.',
            $templateFileContent,
            __METHOD__
        );
        throw new ShouldNotHappenException($errorMessage);
    }
}
