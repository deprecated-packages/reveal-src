<?php

declare(strict_types=1);

namespace Reveal\TwigPHPStanCompiler\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
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

    public function beforeTraverse(array $nodes)
    {
        $this->doDisplayStmts = [];

        return $nodes;
    }

    public function enterNode(Node $node)
    {
        if (! $node instanceof ClassMethod) {
            return null;
        }

        if (! $this->simpleNameResolver->isNames($node, ['doDisplay', 'block_*'])) {
            return null;
        }

        if ($node->stmts === null) {
            return null;
        }

        foreach ($node->stmts as $stmt) {
            if ($this->isMacrosAssign($stmt)) {
                $docComment = $stmt->getDocComment();
                if ($docComment === null) {
                    continue;
                }
                // keep @var doc types
                $nop = new Nop();
                $nop->setDocComment($docComment);
                $this->doDisplayStmts[] = $nop;
                continue;
            }

            if ($stmt instanceof Expression && $stmt->expr instanceof FuncCall) {
                $funcCall = $stmt->expr;
                if ($this->simpleNameResolver->isName($funcCall, 'extract')) {
                    continue;
                }
            }

            // unwrap "echo twig_escape_filter(..., $variable);"
            // to "echo $variable;"
            if ($stmt instanceof Echo_) {
                $onlyExpr = $stmt->exprs[0];
                if ($onlyExpr instanceof FuncCall && $this->simpleNameResolver->isName($onlyExpr, 'twig_escape_filter')) {
                    $funcCall = $onlyExpr;

                    $stmt->exprs = [$funcCall->getArgs()[1]->value];
                }
            }

            $this->doDisplayStmts[] = $stmt;
        }

        return null;
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
        if (! $stmt instanceof Expression) {
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
