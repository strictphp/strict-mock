<?php

declare(strict_types=1);

namespace LaraStrict\Laravel;

use Illuminate\Support\ServiceProvider;
use LaraStrict\Testing\Actions\GetBasePathForStubsAction;
use LaraStrict\Testing\Actions\GetNamespaceForStubsAction;
use LaraStrict\Testing\Commands\MakeExpectationCommand;
use LaraStrict\Testing\Contracts\GetBasePathForStubsActionContract;
use LaraStrict\Testing\Contracts\GetNamespaceForStubsActionContract;

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
