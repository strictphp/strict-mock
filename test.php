<?php declare(strict_types=1);

use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Nette\Utils\Finder;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TypeParser;
use StrictPhp\StrictMock\PHPUnit\Services\TestFrameworkService;
use StrictPhp\StrictMock\Symfony\Factories\FinderFactory;
use StrictPhp\StrictMock\Testing\Actions\AddUseByTypeAction;
use StrictPhp\StrictMock\Testing\Actions\FilePathToClassAction;
use StrictPhp\StrictMock\Testing\Actions\FindAllGeneratedAssertClassesAction;
use StrictPhp\StrictMock\Testing\Actions\VendorClassToRelativeAction;
use StrictPhp\StrictMock\Testing\Actions\WritePhpFileAction;
use StrictPhp\StrictMock\Testing\Assert\Actions\GenerateAssertClassAction;
use StrictPhp\StrictMock\Testing\Assert\Actions\GenerateAssertMethodAction;
use StrictPhp\StrictMock\Testing\Assert\Actions\RemoveAssertFileAction;
use StrictPhp\StrictMock\Testing\Assert\Factories\AssertFileStateEntityFactory;
use StrictPhp\StrictMock\Testing\Assert\Factories\AssertObjectEntityFactory;
use StrictPhp\StrictMock\Testing\Exceptions\IgnoreAssertException;
use StrictPhp\StrictMock\Testing\Expectation\Actions\ExpectationFileContentAction;
use StrictPhp\StrictMock\Testing\Expectation\Factories\ExpectationObjectEntityFactory;
use StrictPhp\StrictMock\Testing\Factories\PhpDocEntityFactory;
use StrictPhp\StrictMock\Testing\Factories\PhpFileFactory;
use StrictPhp\StrictMock\Testing\Factories\ProjectSetupEntityFactory;
use StrictPhp\StrictMock\Testing\Factories\ReflectionClassFactory;
use StrictPhp\StrictMock\Testing\Services\ComposerJsonService;
use StrictPhp\StrictMock\Testing\Services\ComposerPsr4Service;
use StrictPhp\StrictMock\Testing\Transformers\ReflectionClassToFileSetupEntity;

require __DIR__ . '/vendor/autoload.php';

/**
 * @return Generator<string>
 */
function loadFiles(string $dir, string $root): Generator
{
    $finder = Finder::findFiles('*Contract.php')
        ->from($dir);

    foreach ($finder as $file) {
        yield ltrim(str_replace($root, '', $file->getPathname()), DIRECTORY_SEPARATOR);
    }
}

$fileArg = $argv[1] ?? null;

{ // setup
    $fromExists = true;
    $composerDir = __DIR__;
    $exportDefaultDir = $composerDir . '/tests/Feature';

    $files = loadFiles($composerDir . '/src', $composerDir);
}

{ // DI
    $composerJsonService = new ComposerJsonService();
    $composerPsr4Service = new ComposerPsr4Service($composerJsonService);
    $filePathToClassAction = new FilePathToClassAction($composerPsr4Service);

    $setup = (new ProjectSetupEntityFactory($composerDir, $composerPsr4Service, $exportDefaultDir))->create();

    $localFilesystem = new LocalFilesystemAdapter('/');
    $league = new Filesystem($localFilesystem);
    $filesystem = new FilesystemAdapter($league, $localFilesystem);
    $writePhpFileAction = new WritePhpFileAction($filesystem);

    $reflectionClassToFileSetupEntity = new ReflectionClassToFileSetupEntity($setup, $filesystem, $composerPsr4Service);
    $phpFileFactory = new PhpFileFactory();
    $assertObjectEntityFactory = new AssertObjectEntityFactory($reflectionClassToFileSetupEntity, $phpFileFactory);

    $phpDocEntityFactory = new PhpDocEntityFactory(new Lexer(), new PhpDocParser(new TypeParser(), new ConstExprParser()));
    $expectationFileContentAction = new ExpectationFileContentAction();

    $removeAssertFileAction = new RemoveAssertFileAction();

    $generateAssertMethodAction = new GenerateAssertMethodAction(new TestFrameworkService());
    $assertFileStateEntityFactory = new AssertFileStateEntityFactory($assertObjectEntityFactory);
    $generateAssertClass = new GenerateAssertClassAction(
        $assertFileStateEntityFactory,
        $phpDocEntityFactory,
        $expectationFileContentAction,
        $writePhpFileAction,
        $generateAssertMethodAction
    );

    $finderFactory = new FinderFactory();
    $findAllClassesAction = new FindAllGeneratedAssertClassesAction(
        $finderFactory,
        $filePathToClassAction,
        $setup,
    );
}


function render(GenerateAssertClassAction $generateAssertClass, iterable $files, ?Closure $transformer = null): void
{
    if ($transformer === null) {
        $transformer = static fn ($v) => $v;
    }

    foreach ($files as $file) {
        $results = [];
        try {
            $results = $generateAssertClass->execute(
                $transformer($file)
            );
        } catch (IgnoreAssertException $e) {
            echo sprintf('Skipped, class is ignored "%s".%s', $e->getMessage(), PHP_EOL);
        }

        foreach ($results as $source) {
            echo sprintf('Class %s as file %s%s', $source->class, $source->pathname, PHP_EOL);
        }
    }
}

if ($fileArg !== null) {
    $reflectionClassFactory = new ReflectionClassFactory($setup, $filePathToClassAction);
    $files = [$fileArg];
    render($generateAssertClass, $files, static fn ($file) => $reflectionClassFactory->create($file));
} elseif ($fromExists) {
    render($generateAssertClass, $findAllClassesAction->execute());
} else {
    $reflectionClassFactory = new ReflectionClassFactory($setup, $filePathToClassAction);
    render($generateAssertClass, $files, static fn ($file) => $reflectionClassFactory->create($file));
}
