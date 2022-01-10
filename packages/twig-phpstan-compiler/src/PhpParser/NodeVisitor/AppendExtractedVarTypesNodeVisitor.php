<?php

declare(strict_types=1);

namespace Reveal\TwigPHPStanCompiler\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;
use Reveal\TemplatePHPStanCompiler\NodeFactory\VarDocNodeFactory;
use Reveal\TemplatePHPStanCompiler\ValueObject\VariableAndType;
use Symplify\Astral\Naming\SimpleNameResolver;

final class AppendExtractedVarTypesNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @param VariableAndType[] $variablesAndTypes
     */
    public function __construct(
        private SimpleNameResolver $simpleNameResolver,
        private VarDocNodeFactory $varDocNodeFactory,
        private array $variablesAndTypes
    ) {
    }

    public function enterNode(Node $node): Node|null
    {
        // look for "doDisplay()"
        if (! $node instanceof ClassMethod) {
            return null;
        }

        if (! $this->simpleNameResolver->isNames($node, ['doDisplay', 'block_*'])) {
            return null;
        }

        $docNodes = $this->varDocNodeFactory->createDocNodes($this->variablesAndTypes);

        // needed to ping phpstan about possible invisbile variables
        $extractFuncCall = new FuncCall(new Name('extract'));
        $extractFuncCall->args[] = new Arg(new Variable('context'));
        $funcCallExpression = new Expression($extractFuncCall);

        $node->stmts = array_merge([$funcCallExpression], $docNodes, (array) $node->stmts);
        return $node;
    }
}
