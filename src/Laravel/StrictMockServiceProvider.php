<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Laravel;

use Illuminate\Support\ServiceProvider;
use LaraStrict\StrictMock\Laravel\Commands\MakeExpectationCommand;
use LaraStrict\StrictMock\Testing\Actions\GetBasePathForStubsAction;
use LaraStrict\StrictMock\Testing\Actions\GetNamespaceForStubsAction;
use LaraStrict\StrictMock\Testing\Contracts\GetBasePathForStubsActionContract;
use LaraStrict\StrictMock\Testing\Contracts\GetNamespaceForStubsActionContract;

final class StrictMockServiceProvider extends ServiceProvider
{
    public array $singletons = [
        GetBasePathForStubsActionContract::class => GetBasePathForStubsAction::class,
        GetNamespaceForStubsActionContract::class => GetNamespaceForStubsAction::class,
    ];


    // TODO register command
    public function register(): void
    {
        parent::register();

        if ($this->app->environment(['testing', 'local'])) {
            $this->commands([MakeExpectationCommand::class]);
        }
    }
}
