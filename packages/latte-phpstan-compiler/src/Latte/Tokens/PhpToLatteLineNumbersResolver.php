<?php

declare(strict_types=1);

namespace Reveal\LattePHPStanCompiler\Latte\Tokens;

use PhpParser\NodeTraverser;
use Reveal\LattePHPStanCompiler\PhpParser\NodeVisitor\LatteLineNumberNodeVisitor;
use Symplify\Astral\PhpParser\SmartPhpParser;

final class PhpToLatteLineNumbersResolver
{
    public function __construct(
        private LatteLineNumberNodeVisitor $latteLineNumberNodeVisitor,
        private SmartPhpParser $smartPhpParser
    ) {
    }

    /**
     * Here we have to use file content and parse it again, so we have updated start line positions
     *
     * @return array<int, int>
     */
    public function resolve(string $phpFileContent): array
    {
        $phpNodes = $this->smartPhpParser->parseString($phpFileContent);

        // nothign to resolve
        if ($phpNodes === []) {
            return [];
        }

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->latteLineNumberNodeVisitor);
        $nodeTraverser->traverse($phpNodes);

        return $this->latteLineNumberNodeVisitor->getPhpLinesToLatteLines();
    }
}
