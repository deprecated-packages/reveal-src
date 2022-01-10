<?php

declare(strict_types=1);

namespace Reveal\TwigPHPStanCompiler\PhpParser\NodeVisitor\Normalization;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;
use Symplify\Astral\Naming\SimpleNameResolver;

final class LoadTemplateNormalizeNodeVisitor extends NodeVisitorAbstract
{
    public function __construct(
        private SimpleNameResolver $simpleNameResolver
    ) {
    }

    public function enterNode(Node $node)
    {
        if (! $node instanceof MethodCall) {
            return null;
        }

        if (! $this->simpleNameResolver->isName($node->name, 'display')) {
            return null;
        }

        if (! $node->var instanceof MethodCall) {
            return null;
        }

        $loadTemplateMethodCall = $node->var;
        $templateExpr = $loadTemplateMethodCall->getArgs()[0]->value;

        // complete parameters
        $args = [
            new Arg($templateExpr),
        ];

        $firstArgValue = $node->getArgs()[0]->value;
        // func call merge @todo
        if ($firstArgValue instanceof FuncCall && $this->simpleNameResolver->isName($firstArgValue, 'twig_array_merge')) {
            $parameterArray = $firstArgValue->getArgs()[1]->value;
            $args[] = new Arg($parameterArray);
        }

        return new FuncCall(new Name('load_template'), $args);
    }
}
