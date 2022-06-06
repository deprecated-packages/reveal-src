<?php

declare(strict_types=1);

namespace Reveal\TwigPHPStanCompiler\PhpParser\NodeVisitor\Normalization;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeVisitorAbstract;
use Reveal\TwigPHPStanCompiler\Contract\NodeVisitor\NormalizingNodeVisitorInterface;
use Symplify\Astral\Naming\SimpleNameResolver;

final class UnwrapEscapeFilterNormalizeNodeVisitor extends NodeVisitorAbstract implements
 NormalizingNodeVisitorInterface
{
    public function __construct(
        private SimpleNameResolver $simpleNameResolver
    ) {
    }

    public function enterNode(Node $node)
    {
        if (! $node instanceof FuncCall) {
            return null;
        }

        if (! $this->simpleNameResolver->isName($node->name, 'twig_escape_filter')) {
            return null;
        }

        return $node->getArgs()[1]->value;
    }
}
