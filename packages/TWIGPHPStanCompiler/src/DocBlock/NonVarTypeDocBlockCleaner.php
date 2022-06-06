<?php

declare(strict_types=1);

namespace Reveal\TwigPHPStanCompiler\DocBlock;

use Nette\Utils\Strings;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Twig\Token;
use Twig\TokenStream;

final class NonVarTypeDocBlockCleaner
{
    /**
     * @var string
     * @see https://regex101.com/r/dsL5Ou/1
     */
    public const TWIG_VAR_TYPE_DOCBLOCK_REGEX = '#\{\#\s+@var\s+(?<name>.*?)\s+(?<type>.*?)\s+\#}#';

    /**
     * @var string
     * @see https://regex101.com/r/shHvbH/1
     */
    private const COMMENT_START_REGEX = '#(\s+)?\{\##';

    public function __construct(
        private PrivatesAccessor $privatesAccessor,
    ) {
    }

    public function cleanTokenStream(TokenStream $tokenStream): TokenStream
    {
        /** @var Token[] $tokens */
        $tokens = $this->privatesAccessor->getPrivateProperty($tokenStream, 'tokens');

        foreach ($tokens as $key => $token) {
            if ($token->getType() !== Token::TEXT_TYPE) {
                continue;
            }

            // is comment text?
            if (! Strings::match($token->getValue(), self::COMMENT_START_REGEX)) {
                continue;
            }

            $match = Strings::match($token->getValue(), self::TWIG_VAR_TYPE_DOCBLOCK_REGEX);
            if ($match !== null) {
                continue;
            }

            unset($tokens[$key]);
        }

        $tokens = array_values($tokens);

        return new TokenStream($tokens, $tokenStream->getSourceContext());
    }
}
