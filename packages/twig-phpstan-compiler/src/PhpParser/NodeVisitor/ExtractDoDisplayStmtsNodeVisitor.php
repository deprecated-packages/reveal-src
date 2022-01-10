<?php

declare(strict_types=1);

namespace Reveal\TwigPHPStanCompiler\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Symplify\Astral\Naming\SimpleNameResolver;

final class ExtractDoDisplayStmtsNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var Stmt[]
     */
    private array $doDisplayStmts = [];

    public function __construct(
        private SimpleNameResolver $simpleNameResolver
    ) {
    }

    public function enterNode(Node $node)
    {
        if (! $node instanceof ClassMethod) {
            return null;
        }

        if (! $this->simpleNameResolver->isName($node, 'doDisplay')) {
            return null;
        }

        if ($node->stmts === null) {
            return null;
        }

        foreach ($node->stmts as $stmt) {
            if ($this->isMacrosAssign($stmt)) {
                continue;
            }

            // unwrap echo twig_escape_filter()
            if ($stmt instanceof Stmt\Echo_) {
                $onlyExpr = $stmt->exprs[0];
                if ($onlyExpr instanceof Node\Expr\FuncCall && $this->simpleNameResolver->isName($onlyExpr, 'twig_escape_filter')) {
                    $funcCall = $onlyExpr;

                    $stmt->exprs = [$funcCall->getArgs()[1]->value];
                }
            }

            // inline $this->loadTemplate() to include_once
            // @todo

            $this->doDisplayStmts[] = $stmt;
        }

        return NodeTraverser::STOP_TRAVERSAL;
    }

    /**
     * @return Stmt[]
     */
    public function getDoDisplayStmts(): array
    {
        return $this->doDisplayStmts;
    }

    private function isMacrosAssign(Stmt $stmt): bool
    {
        if (! $stmt instanceof Stmt\Expression) {
            return false;
        }

        $expr = $stmt->expr;
        if (! $expr instanceof Assign) {
            return false;
        }

        if (! $expr->var instanceof Variable) {
            return false;
        }

        return $this->simpleNameResolver->isName($expr->var, 'macros');
    }
}
