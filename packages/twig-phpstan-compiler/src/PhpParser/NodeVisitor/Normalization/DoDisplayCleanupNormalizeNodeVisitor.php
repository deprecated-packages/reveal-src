<?php

declare(strict_types=1);

namespace Reveal\TwigPHPStanCompiler\PhpParser\NodeVisitor\Normalization;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Unset_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Reveal\TwigPHPStanCompiler\Contract\NodeVisitor\NormalizingNodeVisitorInterface;
use Symplify\Astral\Naming\SimpleNameResolver;

final class DoDisplayCleanupNormalizeNodeVisitor extends NodeVisitorAbstract implements
 NormalizingNodeVisitorInterface
{
    public function __construct(
        private SimpleNameResolver $simpleNameResolver
    ) {
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Unset_) {
            return NodeTraverser::REMOVE_NODE;
        }

        if (! $node instanceof Expression) {
            return null;
        }

        if ($node->expr instanceof Node\Expr\FuncCall) {
            $funcCall = $node->expr;
            if ($this->simpleNameResolver->isName($funcCall->name, 'extract')) {
                return NodeTraverser::REMOVE_NODE;
            }
        }

        $expr = $node->expr;
        if ($expr instanceof Assign) {
            if ($expr->var instanceof Variable) {
                $variable = $expr->var;
                if ($this->simpleNameResolver->isNames($variable, ['_parent', 'context'])) {
                    return NodeTraverser::REMOVE_NODE;
                }
            }
        }

        return null;
    }
}
