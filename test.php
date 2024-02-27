<?php declare(strict_types=1);

use LaraStrict\StrictMock\PHPUnit\Services\TestFrameworkService;
use LaraStrict\StrictMock\Symfony\Factories\FinderFactory;
use LaraStrict\StrictMock\Testing\Actions\AddUseByTypeAction;
use LaraStrict\StrictMock\Testing\Actions\ComposerPsr4Action;
use LaraStrict\StrictMock\Testing\Actions\FilePathToClassAction;
use LaraStrict\StrictMock\Testing\Actions\FindAllGeneratedAssertClassesAction;
use LaraStrict\StrictMock\Testing\Actions\MkDirAction;
use LaraStrict\StrictMock\Testing\Actions\VendorClassToRelativeAction;
use LaraStrict\StrictMock\Testing\Actions\WritePhpFileAction;
use LaraStrict\StrictMock\Testing\Assert\Actions\GenerateAssertClassAction;
use LaraStrict\StrictMock\Testing\Assert\Actions\GenerateAssertMethodAction;
use LaraStrict\StrictMock\Testing\Assert\Actions\RemoveAssertFileAction;
use LaraStrict\StrictMock\Testing\Assert\Factories\AssertFileStateEntityFactory;
use LaraStrict\StrictMock\Testing\Assert\Factories\AssertObjectEntityFactory;
use LaraStrict\StrictMock\Testing\Entities\FileSetupEntity;
use LaraStrict\StrictMock\Testing\Entities\ProjectSetupEntity;
use LaraStrict\StrictMock\Testing\Exceptions\IgnoreAssertException;
use LaraStrict\StrictMock\Testing\Expectation\Actions\ExpectationFileContentAction;
use LaraStrict\StrictMock\Testing\Expectation\Factories\ExpectationObjectEntityFactory;
use LaraStrict\StrictMock\Testing\Factories\PhpDocEntityFactory;
use LaraStrict\StrictMock\Testing\Factories\PhpFileFactory;
use LaraStrict\StrictMock\Testing\Factories\ReflectionClassFactory;
use LaraStrict\StrictMock\Testing\Services\ComposerJsonService;
use LaraStrict\StrictMock\Testing\Transformers\ReflectionClassToFileSetupEntity;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TypeParser;

require __DIR__ . '/vendor/autoload.php';

{ // setup
    $fromExists = true;
    $files = [
        'src/Testing/Contracts/FindAllClassesActionContract.php',
        'src/Testing/Contracts/ComposerJsonServiceContract.php',
    ];
    $composerDir = __DIR__;
    $projectDir = $composerDir . '/src';
    $exportDir = $composerDir . '/tests/Feature';
}

{ // DI
    $addUseByType = new AddUseByTypeAction();
    $composerJsonService = new ComposerJsonService();
    $composerPsr4Action = new ComposerPsr4Action($composerJsonService);
    $filePathToClassAction = new FilePathToClassAction($composerPsr4Action);
    $vendorClassToRelativeAction = new VendorClassToRelativeAction($composerPsr4Action);

    $projectRoot = new FileSetupEntity($projectDir, $filePathToClassAction->execute($projectDir));
    $exportRoot = new FileSetupEntity($exportDir, $filePathToClassAction->execute($exportDir));
    $setup = new ProjectSetupEntity($composerDir, $projectRoot, $exportRoot);

    $mkDirAction = new MkDirAction();
    $phpFileFactory = new PhpFileFactory();
    $writePhpFileAction = new WritePhpFileAction($mkDirAction);

    $reflectionClassToFileSetupEntity = new ReflectionClassToFileSetupEntity($setup, $mkDirAction);
    $expectationObjectEntityFactory = new ExpectationObjectEntityFactory($phpFileFactory);
    $assertObjectEntityFactory = new AssertObjectEntityFactory($reflectionClassToFileSetupEntity, $phpFileFactory);

    $phpDocEntityFactory = new PhpDocEntityFactory(new PhpDocStringResolver(new Lexer(), new PhpDocParser(new TypeParser(), new ConstExprParser())));
    $expectationFileContentAction = new ExpectationFileContentAction($expectationObjectEntityFactory, $writePhpFileAction, $addUseByType);

    $removeAssertFileAction = new RemoveAssertFileAction();

    $generateAssertMethodAction = new GenerateAssertMethodAction(new TestFrameworkService(), $addUseByType);
    $assertFileStateEntityFactory = new AssertFileStateEntityFactory($assertObjectEntityFactory);
    $generateAssertClass = new GenerateAssertClassAction(
        $removeAssertFileAction,
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

if ($fromExists) {
    render($generateAssertClass, $findAllClassesAction->execute());
} else {
    $reflectionClassFactory = new ReflectionClassFactory($setup, $filePathToClassAction);
    render($generateAssertClass, $files, static fn ($file) => $reflectionClassFactory->create($file));
}
