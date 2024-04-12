<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Factories;

use LaraStrict\StrictMock\Testing\Entities\PhpDocEntity;
use LaraStrict\StrictMock\Testing\Enums\PhpType;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use ReflectionMethod;

final class PhpDocEntityFactory
{
    public function __construct(
        private readonly Lexer $lexer,
        private readonly PhpDocParser $phpDocParser
    )
    {
    }

    public function create(ReflectionMethod $method): PhpDocEntity
    {
        $comment = $method->getDocComment();
        if ($comment === false) {
            return new PhpDocEntity();
        }

        $tokens = new TokenIterator($this->lexer->tokenize($comment));
        $doc = $this->phpDocParser->parse($tokens);
        $tokens->consumeTokenType(Lexer::TOKEN_END);

        $returnTags = $doc->getReturnTagValues();
        $returnType = PhpType::Unknown;

        if ($returnTags !== []) {
            $name = (string) $returnTags[0]->type;
            $returnType = match ($name) {
                '$this', 'static' => PhpType::Static,
                'self' => PhpType::Self,
                'void' => PhpType::Void,
                default => PhpType::Mixed,
            };
        }

        return new PhpDocEntity(returnType: $returnType);
    }
}
