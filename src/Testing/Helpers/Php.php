<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Helpers;

final class Php
{
    public static function existClassInterfaceEnum(string $class): bool
    {
        // trait_exists()
        return self::existClassInterface($class) || enum_exists($class);
    }

    public static function existClassInterface(string $class): bool
    {
        return class_exists($class) || interface_exists($class);
    }

}
