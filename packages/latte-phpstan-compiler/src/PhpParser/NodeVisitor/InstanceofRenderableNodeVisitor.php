<?php

declare(strict_types=1);

namespace Symplify\LattePHPStanCompiler\PhpParser\NodeVisitor;

use Nette\Application\UI\Renderable;
use PhpParser\Node;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Symplify\Astral\Naming\SimpleNameResolver;
use Symplify\LattePHPStanCompiler\Contract\LatteToPhpCompilerNodeVisitorInterface;

/**
 * Fixes render() invalid contract
 *
 * @see https://github.com/symplify/symplify/issues/3682
 */
final class InstanceofRenderableNodeVisitor extends NodeVisitorAbstract implements LatteToPhpCompilerNodeVisitorInterface
{
    public function __construct(
        private SimpleNameResolver $simpleNameResolver,
    ) {
    }

    public function leaveNode(Node $node): Node|null|int
    {
        if (! $node instanceof If_) {
            return null;
        }

        if ($node->elseifs !== []) {
            return null;
        }

        if (! $node->cond instanceof Instanceof_) {
            return null;
        }

        $instanceof = $node->cond;

        if (! $this->simpleNameResolver->isNames(
            $instanceof->class,
            ['Nette\Application\UI\IRenderable', Renderable::class]
        )) {
            return null;
        }

        $redrawMethodCall = $this->matchRedrawControlMethodCall($node);
        if (! $redrawMethodCall instanceof MethodCall) {
            return null;
        }

        return NodeTraverser::REMOVE_NODE;
    }

    private function matchRedrawControlMethodCall(If_ $if): ?MethodCall
    {
        if ($if->stmts === []) {
            return null;
        }

        $onlyStmt = $if->stmts[0];
        if (! $onlyStmt instanceof Expression) {
            return null;
        }

        $stmtExpr = $onlyStmt->expr;
        if (! $stmtExpr instanceof MethodCall) {
            return null;
        }

        return $stmtExpr;
    }
}
