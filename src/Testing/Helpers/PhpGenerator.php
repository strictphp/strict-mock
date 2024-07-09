<?php declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Helpers;

use Nette\PhpGenerator\Method;

final class PhpGenerator
{
    public const Constructor = '__construct';

    public static function buildConstructor(bool $callParent = true): Method
    {
        $method = (new Method(self::Constructor))
            ->setPublic();
        if ($callParent) {
            $method->addBody('parent::__construct();');
        }

        return $method;
    }
}
