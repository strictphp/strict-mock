<?php

declare(strict_types=1);

namespace Tests\LaraStrict\Unit\Testing\Actions;

use Illuminate\Foundation\Application;
use LaraStrict\Testing\Actions\GetBasePathForStubsAction;
use PHPUnit\Framework\TestCase;

class GetBasePathForStubsActionTest extends TestCase
{
    public function testExecute(): void
    {
        $action = new GetBasePathForStubsAction(new Application('base'));
        $this->assertEquals('base', $action->execute());
    }
}
