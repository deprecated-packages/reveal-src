<?php

declare(strict_types=1);

namespace Reveal\TwigPHPStanCompiler\ErrorReporting;

use PhpParser\NodeTraverser;
use Reveal\TwigPHPStanCompiler\PhpParser\NodeVisitor\PhpToTemplateLinesNodeVisitor;
use Symplify\Astral\PhpParser\SmartPhpParser;

final class TemplateLinesMapResolver
{
    public function __construct(
        private SmartPhpParser $smartPhpParser,
    ) {
    }

    /**
     * @return array<int, int>
     */
    public function resolve(string $phpContent): array
    {
        $stmts = $this->smartPhpParser->parseString($phpContent);

        $phpToTemplateLinesNodeVisitor = new PhpToTemplateLinesNodeVisitor();

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($phpToTemplateLinesNodeVisitor);
        $nodeTraverser->traverse($stmts);

        return $phpToTemplateLinesNodeVisitor->getPhpLinesToTemplateLines();
    }
}
