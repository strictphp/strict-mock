<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Actions;

use LaraStrict\StrictMock\Testing\Exceptions\LogicException;
use LaraStrict\StrictMock\Testing\Helpers\Php;
use Nette\PhpGenerator\PhpNamespace;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

final class AddUseByTypeAction
{
    public function execute(PhpNamespace $namespace, ?ReflectionType $type): void
    {
        if ($type === null) {
            return;
        } elseif (class_exists(ReflectionIntersectionType::class) && $type instanceof ReflectionIntersectionType) {
            $types = $type->getTypes();
        } elseif ($type instanceof ReflectionUnionType) {
            $types = $type->getTypes();
        } elseif ($type instanceof ReflectionNamedType) {
            $types = [$type];
        } else {
            throw new LogicException('Missing type %s', $type::class);
        }

        foreach ($types as $_type) {
            $class = $_type->getName();
            if (Php::existClassInterfaceEnum($class)) {
                $namespace->addUse($class);
            }
        }
    }
}
