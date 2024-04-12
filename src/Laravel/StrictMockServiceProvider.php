<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Laravel;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use LaraStrict\StrictMock\Laravel\Commands\MakeExpectationCommand;
use LaraStrict\StrictMock\PHPUnit\Services\TestFrameworkService;
use LaraStrict\StrictMock\Symfony\Factories\FinderFactory;
use LaraStrict\StrictMock\Testing\Actions\FilePathToClassAction;
use LaraStrict\StrictMock\Testing\Actions\FindAllGeneratedAssertClassesAction;
use LaraStrict\StrictMock\Testing\Contracts\ComposerJsonServiceContract;
use LaraStrict\StrictMock\Testing\Contracts\FindAllGeneratedAssertClassesActionContract;
use LaraStrict\StrictMock\Testing\Contracts\FinderFactoryContract;
use LaraStrict\StrictMock\Testing\Contracts\TestFrameworkServiceContract;
use LaraStrict\StrictMock\Testing\Entities\FileSetupEntity;
use LaraStrict\StrictMock\Testing\Entities\ProjectSetupEntity;
use LaraStrict\StrictMock\Testing\Helpers\Realpath;
use LaraStrict\StrictMock\Testing\Services\ComposerJsonService;

final class StrictMockServiceProvider extends ServiceProvider
{
    public array $singletons = [
        ComposerJsonServiceContract::class => ComposerJsonService::class,
        FindAllGeneratedAssertClassesActionContract::class => FindAllGeneratedAssertClassesAction::class,
        FinderFactoryContract::class => FinderFactory::class,
        TestFrameworkServiceContract::class => TestFrameworkService::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->singleton(ProjectSetupEntity::class, static function (Application $app) {
            $fileToClassAction = $app->make(FilePathToClassAction::class);
            assert($fileToClassAction instanceof FilePathToClassAction);
            $composerDir = Realpath::make(__DIR__ . '/../..');
            $projectDir = $composerDir . '/app';
            $project = new FileSetupEntity($projectDir, $fileToClassAction->execute($projectDir));
            return new ProjectSetupEntity($composerDir, $project);
        });

        if ($this->app->environment(['testing', 'local'])) {
            $this->commands([MakeExpectationCommand::class]);
        }
    }
}
