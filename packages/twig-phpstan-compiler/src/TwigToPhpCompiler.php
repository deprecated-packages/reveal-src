<?php

declare(strict_types=1);

namespace Reveal\TwigPHPStanCompiler;

use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Reveal\TwigPHPStanCompiler\DocBlock\NonVarTypeDocBlockCleaner;
use Reveal\TwigPHPStanCompiler\ErrorReporting\TemplateLinesMapResolver;
use Reveal\TwigPHPStanCompiler\Exception\TwigPHPStanCompilerException;
use Reveal\TwigPHPStanCompiler\PhpParser\NodeVisitor\CollectForeachedVariablesNodeVisitor;
use Reveal\TwigPHPStanCompiler\PhpParser\NodeVisitor\ExpandForeachContextNodeVisitor;
use Reveal\TwigPHPStanCompiler\PhpParser\NodeVisitor\ExtractDoDisplayStmtsNodeVisitor;
use Reveal\TwigPHPStanCompiler\PhpParser\NodeVisitor\RemoveUselessClassMethodsNodeVisitor;
use Reveal\TwigPHPStanCompiler\PhpParser\NodeVisitor\ReplaceEchoWithVarDocTypeNodeVisitor;
use Reveal\TwigPHPStanCompiler\PhpParser\NodeVisitor\TwigGetAttributeExpanderNodeVisitor;
use Reveal\TwigPHPStanCompiler\PhpParser\NodeVisitor\UnwrapCoalesceContextNodeVisitor;
use Reveal\TwigPHPStanCompiler\PhpParser\NodeVisitor\UnwrapContextVariableNodeVisitor;
use Reveal\TwigPHPStanCompiler\PhpParser\NodeVisitor\UnwrapTwigEnsureTraversableNodeVisitor;
use Reveal\TwigPHPStanCompiler\Reflection\PublicPropertyAnalyzer;
use Reveal\TwigPHPStanCompiler\Twig\TolerantTwigEnvironment;
use Symplify\Astral\Naming\SimpleNameResolver;
use Symplify\SmartFileSystem\SmartFileSystem;
use Symplify\TemplatePHPStanCompiler\ValueObject\PhpFileContentsWithLineMap;
use Symplify\TemplatePHPStanCompiler\ValueObject\VariableAndType;
use Twig\Lexer;
use Twig\Loader\ArrayLoader;
use Twig\Node\ModuleNode;
use Twig\Node\Node;
use Twig\Source;

/**
 * @see \Reveal\TwigPHPStanCompiler\Tests\TwigToPhpCompiler\TwigToPhpCompilerTest
 */
final class TwigToPhpCompiler
{
    private Parser $parser;

    public function __construct(
        private SmartFileSystem $smartFileSystem,
        private Standard $printerStandard,
        private TwigVarTypeDocBlockDecorator $twigVarTypeDocBlockDecorator,
        private SimpleNameResolver $simpleNameResolver,
        private ObjectTypeMethodAnalyzer $objectTypeMethodAnalyzer,
        private PublicPropertyAnalyzer $publicPropertyAnalyzer,
        private TemplateLinesMapResolver $templateLinesMapResolver,
        private NonVarTypeDocBlockCleaner $nonVarTypeDocBlockCleaner,
        private ExtractDoDisplayStmtsNodeVisitor $extractDoDisplayStmtsNodeVisitor,
    ) {
        // avoids unneeded caching from phpstan parser, we need to change content of same file based on provided variable types
        $parserFactory = new ParserFactory();
        $this->parser = $parserFactory->create(ParserFactory::PREFER_PHP7);
    }

    /**
     * @param array<VariableAndType> $variablesAndTypes
     */
    public function compileContent(string $filePath, array $variablesAndTypes): PhpFileContentsWithLineMap
    {
        $fileContent = $this->smartFileSystem->readFile($filePath);
        $tolerantTwigEnvironment = $this->createTwigEnvironment($filePath, $fileContent);

        $moduleNode = $this->parseFileContentToModuleNode($tolerantTwigEnvironment, $fileContent, $filePath);
        $rawPhpContent = $tolerantTwigEnvironment->compile($moduleNode);

        $decoratedPhpContent = $this->decoratePhpContent($rawPhpContent, $variablesAndTypes);
        $phpLinesToTemplateLines = $this->templateLinesMapResolver->resolve($decoratedPhpContent);

        return new PhpFileContentsWithLineMap($decoratedPhpContent, $phpLinesToTemplateLines);
    }

    private function createTwigEnvironment(string $filePath, string $fileContent): TolerantTwigEnvironment
    {
        $arrayLoader = new ArrayLoader([
            $filePath => $fileContent,
        ]);

        return new TolerantTwigEnvironment($arrayLoader);
    }

    /**
     * @return ModuleNode<Node>
     */
    private function parseFileContentToModuleNode(
        TolerantTwigEnvironment $tolerantTwigEnvironment,
        string $fileContent,
        string $filePath
    ): ModuleNode {
        // this should disable comments as we know it - if we don't change it here, the tokenizer will remove all comments completely
        $lexer = new Lexer($tolerantTwigEnvironment, [
            'tag_comment' => ['{*', '*}'],
        ]);
        $tolerantTwigEnvironment->setLexer($lexer);

        $tokenStream = $tolerantTwigEnvironment->tokenize(new Source($fileContent, $filePath));

        $clearTokenStream = $this->nonVarTypeDocBlockCleaner->cleanTokenStream($tokenStream);

        return $tolerantTwigEnvironment->parse($clearTokenStream);
    }

    /**
     * @param VariableAndType[] $variablesAndTypes
     */
    private function decoratePhpContent(string $phpContent, array $variablesAndTypes): string
    {
        $stmts = $this->parser->parse($phpContent);
        if ($stmts === null) {
            throw new TwigPHPStanCompilerException();
        }

        // -1. remove useless class methods
        $removeUselessClassMethodsNodeVisitor = new RemoveUselessClassMethodsNodeVisitor();
        $this->traverseStmtsWithVisitors($stmts, [$removeUselessClassMethodsNodeVisitor]);

        // 0. add types first?
        $this->unwarpMagicVariables($stmts);

        // 1. hacking {# @var variable type #} comments to /** @var types */
        $replaceEchoWithVarDocTypeNodeVisitor = new ReplaceEchoWithVarDocTypeNodeVisitor();
        $this->traverseStmtsWithVisitors($stmts, [$replaceEchoWithVarDocTypeNodeVisitor]);

        // get those types for further analysis
        $collectedVariablesAndTypes = $replaceEchoWithVarDocTypeNodeVisitor->getCollectedVariablesAndTypes();
        $variablesAndTypes = array_merge($variablesAndTypes, $collectedVariablesAndTypes);

        // 3. collect foreached variables to determine nested value :)
        $collectForeachedVariablesNodeVisitor = new CollectForeachedVariablesNodeVisitor($this->simpleNameResolver);
        $this->traverseStmtsWithVisitors($stmts, [$collectForeachedVariablesNodeVisitor]);

        // 2. replace twig_get_attribute with direct access/call
        $twigGetAttributeExpanderNodeVisitor = new TwigGetAttributeExpanderNodeVisitor(
            $this->simpleNameResolver,
            $this->objectTypeMethodAnalyzer,
            $this->publicPropertyAnalyzer,
            $variablesAndTypes,
            $collectForeachedVariablesNodeVisitor->getForeachedVariablesToSingles(),
        );

        $this->traverseStmtsWithVisitors($stmts, [$twigGetAttributeExpanderNodeVisitor]);

        // get do display method contents

        $stmts = $this->extractDoDisplayStmts($stmts);

        $phpContent = $this->printerStandard->prettyPrintFile($stmts);
        return $this->twigVarTypeDocBlockDecorator->decorateTwigContentWithTypes($phpContent, $variablesAndTypes);
    }

    /**
     * @param Stmt[] $stmts
     * @param NodeVisitorAbstract[] $nodeVisitors
     */
    private function traverseStmtsWithVisitors(array $stmts, array $nodeVisitors): void
    {
        $nodeTraverser = new NodeTraverser();
        foreach ($nodeVisitors as $nodeVisitor) {
            $nodeTraverser->addVisitor($nodeVisitor);
        }

        $nodeTraverser->traverse($stmts);
    }

    /**
     * @param Stmt[] $stmts
     */
    private function unwarpMagicVariables(array $stmts): void
    {
        // 1. run $context unwrap first, as needed everywhere
        $unwrapContextVariableNodeVisitor = new UnwrapContextVariableNodeVisitor($this->simpleNameResolver);
        $this->traverseStmtsWithVisitors($stmts, [$unwrapContextVariableNodeVisitor]);

        // 2. unwrap coalesce $context
        $unwrapCoalesceContextNodeVisitor = new UnwrapCoalesceContextNodeVisitor($this->simpleNameResolver);
        $this->traverseStmtsWithVisitors($stmts, [$unwrapCoalesceContextNodeVisitor]);

        // 3. unwrap twig_ensure_traversable()
        $unwrapTwigEnsureTraversableNodeVisitor = new UnwrapTwigEnsureTraversableNodeVisitor(
            $this->simpleNameResolver
        );
        $this->traverseStmtsWithVisitors($stmts, [$unwrapTwigEnsureTraversableNodeVisitor]);

        // 4. expand foreached magic to make type references clear for iterated variables
        $expandForeachContextNodeVisitor = new ExpandForeachContextNodeVisitor($this->simpleNameResolver);
        $this->traverseStmtsWithVisitors($stmts, [$expandForeachContextNodeVisitor]);
    }

    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    private function extractDoDisplayStmts(array $stmts): array
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->extractDoDisplayStmtsNodeVisitor);
        $nodeTraverser->traverse($stmts);

        return $this->extractDoDisplayStmtsNodeVisitor->getDoDisplayStmts();
    }
}
