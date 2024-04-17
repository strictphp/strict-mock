<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Laravel;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use StrictPhp\StrictMock\Laravel\Commands\MakeExpectationCommand;
use StrictPhp\StrictMock\PHPUnit\Services\TestFrameworkService;
use StrictPhp\StrictMock\Symfony\Factories\FinderFactory;
use StrictPhp\StrictMock\Testing\Actions\FilePathToClassAction;
use StrictPhp\StrictMock\Testing\Actions\FindAllGeneratedAssertClassesAction;
use StrictPhp\StrictMock\Testing\Contracts\ComposerJsonServiceContract;
use StrictPhp\StrictMock\Testing\Contracts\FindAllGeneratedAssertClassesActionContract;
use StrictPhp\StrictMock\Testing\Contracts\FinderFactoryContract;
use StrictPhp\StrictMock\Testing\Contracts\TestFrameworkServiceContract;
use StrictPhp\StrictMock\Testing\Entities\FileSetupEntity;
use StrictPhp\StrictMock\Testing\Entities\ProjectSetupEntity;
use StrictPhp\StrictMock\Testing\Helpers\Realpath;
use StrictPhp\StrictMock\Testing\Services\ComposerJsonService;

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
