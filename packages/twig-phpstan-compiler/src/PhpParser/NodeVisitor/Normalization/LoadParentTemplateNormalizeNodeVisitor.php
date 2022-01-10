<?php

declare(strict_types=1);

namespace Reveal\TwigPHPStanCompiler\PhpParser\NodeVisitor\Normalization;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Symplify\Astral\Naming\SimpleNameResolver;

final class LoadParentTemplateNormalizeNodeVisitor extends NodeVisitorAbstract
{
    public function __construct(
        private SimpleNameResolver $simpleNameResolver
    ) {
    }

    public function leaveNode(Node $node)
    {
        if (! $node instanceof Node\Stmt\Expression) {
            return null;
        }

        $expr = $node->expr;
        if ($expr instanceof MethodCall) {
            if ($this->simpleNameResolver->isName($expr->name, 'display')) {
                return NodeTraverser::REMOVE_NODE;
            }
        }

        return null;
    }

    public function enterNode(\PhpParser\Node $node)
    {
        // assign
        // load template method call
        if ($node instanceof Node\Expr\Assign) {
            if ($node->expr instanceof MethodCall) {
                $methodCall = $node->expr;
                if (! $this->simpleNameResolver->isName($methodCall->name, 'loadTemplate')) {
                    return null;
                }

                $templateString = $methodCall->getArgs()[0]->value;
                $args = [
                    new Arg($templateString),
                ];

                return new FuncCall(new Name('load_parent_template'), $args);
            }
        }

        return null;
    }
}
