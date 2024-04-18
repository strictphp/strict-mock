<?php

declare(strict_types=1);

namespace Tests\StrictPhp\StrictMock\Unit\Testing\Services;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use StrictPhp\StrictMock\Testing\Services\ComposerJsonService;
use StrictPhp\StrictMock\Testing\Services\ComposerPsr4Service;

final class ComposerPsr4ServiceTest extends TestCase
{
    public function testBasic(): void
    {
        $composerPsr4Service = new ComposerPsr4Service(
            new ComposerJsonService()
        );

        $auto = $composerPsr4Service->autoload(__DIR__);
        Assert::assertSame([
            'StrictPhp\\StrictMock\\' => '/home/milan/www/strict-mock/src',
        ], $auto);

        $autoDev = $composerPsr4Service->autoloadDev(__DIR__);
        Assert::assertSame([
            'Tests\\StrictPhp\\StrictMock\\' => '/home/milan/www/strict-mock/tests',
        ], $autoDev);

        $paths = [];
        foreach ($composerPsr4Service->tryAll(__DIR__) as $ns => $path) {
            $paths[$ns] = $path;
        }
        Assert::assertSame([
            'StrictPhp\\StrictMock\\' => '/home/milan/www/strict-mock/src',
            'Tests\\StrictPhp\\StrictMock\\' => '/home/milan/www/strict-mock/tests',
        ], $paths);
    }
}
