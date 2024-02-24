<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Factories;

use LaraStrict\StrictMock\Testing\Entities\PhpDocEntity;
use LaraStrict\StrictMock\Testing\Enums\PhpType;
use PHPStan\PhpDoc\PhpDocStringResolver;
use ReflectionMethod;

final class PhpDocEntityFactory
{

    public function __construct(private readonly PhpDocStringResolver $phpDocStringResolver)
    {
    }


    public function create(ReflectionMethod $method): PhpDocEntity
    {
        $comment = $method->getDocComment();
        if ($comment === false) {
            return new PhpDocEntity();
        }

        $doc = $this->phpDocStringResolver->resolve($comment);

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
