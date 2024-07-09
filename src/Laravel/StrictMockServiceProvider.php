<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Laravel;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Local\LocalFilesystemAdapter;
use StrictPhp\StrictMock\Laravel\Commands\MakeExpectationCommand;
use StrictPhp\StrictMock\PHPUnit\Services\TestFrameworkService;
use StrictPhp\StrictMock\Symfony\Factories\FinderFactory;
use StrictPhp\StrictMock\Testing\Actions\FilePathToClassAction;
use StrictPhp\StrictMock\Testing\Actions\FindAllGeneratedAssertClassesAction;
use StrictPhp\StrictMock\Testing\Actions\WritePhpFileAction;
use StrictPhp\StrictMock\Testing\Contracts\ComposerJsonServiceContract;
use StrictPhp\StrictMock\Testing\Contracts\ComposerPsr4ServiceContract;
use StrictPhp\StrictMock\Testing\Contracts\FilePathToClassActionContract;
use StrictPhp\StrictMock\Testing\Contracts\FindAllGeneratedAssertClassesActionContract;
use StrictPhp\StrictMock\Testing\Contracts\FinderFactoryContract;
use StrictPhp\StrictMock\Testing\Contracts\ReflectionClassFactoryContract;
use StrictPhp\StrictMock\Testing\Contracts\TestFrameworkServiceContract;
use StrictPhp\StrictMock\Testing\Entities\FileSetupEntity;
use StrictPhp\StrictMock\Testing\Entities\ProjectSetupEntity;
use StrictPhp\StrictMock\Testing\Factories\ReflectionClassFactory;
use StrictPhp\StrictMock\Testing\Helpers\Realpath;
use StrictPhp\StrictMock\Testing\Services\ComposerJsonService;
use StrictPhp\StrictMock\Testing\Services\ComposerPsr4Service;

final class StrictMockServiceProvider extends ServiceProvider
{
    public array $singletons = [
        ComposerJsonServiceContract::class => ComposerJsonService::class,
        ComposerPsr4ServiceContract::class => ComposerPsr4Service::class,
        FilePathToClassActionContract::class => FilePathToClassAction::class,
        FindAllGeneratedAssertClassesActionContract::class => FindAllGeneratedAssertClassesAction::class,
        FinderFactoryContract::class => FinderFactory::class,
        ReflectionClassFactoryContract::class => ReflectionClassFactory::class,
        TestFrameworkServiceContract::class => TestFrameworkService::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->singleton(ProjectSetupEntity::class, static function (Application $app) {
            $composerDir = Realpath::make(__DIR__ . '/../../../../..');

            $composerPsr4Service = $app->make(ComposerPsr4ServiceContract::class);
            assert($composerPsr4Service instanceof ComposerPsr4ServiceContract);
            $autoload = $composerPsr4Service->autoloadDev($composerDir);
            $ns = array_key_first($autoload);
            $prefixPath = $autoload[$ns];

            $project = new FileSetupEntity($prefixPath, $ns);
            return new ProjectSetupEntity($composerDir, $project);
        });

        $this->app->when(WritePhpFileAction::class)
            ->needs(Filesystem::class)
            ->give(static function () {
                $localFilesystem = new LocalFilesystemAdapter('/');
                $league = new \League\Flysystem\Filesystem($localFilesystem);
                return new FilesystemAdapter($league, $localFilesystem);
            });

        if ($this->app->environment(['testing', 'local'])) {
            $this->commands([MakeExpectationCommand::class]);
        }
    }
}
