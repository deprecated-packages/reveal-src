<?php

declare(strict_types=1);

namespace Reveal\TwigPHPStanCompiler\PhpParser\NodeVisitor\Normalization;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
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
        if ($node instanceof Expression) {
            $expr = $node->expr;
            if ($expr instanceof MethodCall) {
                if ($this->simpleNameResolver->isName($expr->name, 'displayBlock')) {
                    return NodeTraverser::REMOVE_NODE;
                }
            }

            if ($expr instanceof Assign && $this->isContextVariable($expr->expr)) {
                return NodeTraverser::REMOVE_NODE;
            }
        }

        if ($node instanceof Foreach_) {
            if ($node->keyVar instanceof ArrayDimFetch) {
                $arrayDimFetch = $node->keyVar;
                if ($this->isContextVariable($arrayDimFetch->var)) {
                    // remove $context['...'] key
                    $node->keyVar = null;
                }

                return $node;
            }
        }

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

    private function isContextVariable(Expr $expr): bool
    {
        if (! $expr instanceof Variable) {
            return false;
        }

        return $this->simpleNameResolver->isName($expr, 'context');
    }
}
