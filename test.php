<?php declare(strict_types=1);

use LaraStrict\StrictMock\PHPUnit\Services\TestFrameworkService;
use LaraStrict\StrictMock\Testing\Actions\FilePathToClassAction;
use LaraStrict\StrictMock\Testing\Actions\WritePhpFileAction;
use LaraStrict\StrictMock\Testing\Assert\Actions\GenerateAssertClassAction;
use LaraStrict\StrictMock\Testing\Assert\Actions\GenerateAssertMethodAction;
use LaraStrict\StrictMock\Testing\Assert\Actions\RemoveAssertFileAction;
use LaraStrict\StrictMock\Testing\Assert\Factories\AssertFileStateEntityFactory;
use LaraStrict\StrictMock\Testing\Assert\Factories\AssertObjectEntityFactory;
use LaraStrict\StrictMock\Testing\Entities\FileSetupEntity;
use LaraStrict\StrictMock\Testing\Entities\ProjectSetupEntity;
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
    $files = [
        'src/Testing/Contracts/FindAllClassesActionContract.php',
        'src/Testing/Contracts/ComposerJsonServiceContract.php',
    ];
    $projectDir = __DIR__ . '/src';
    $exportDir = __DIR__ . '/tests/Feature';
}

{ // DI

    $composerJsonService = new ComposerJsonService();
    $filePathToClassAction = new FilePathToClassAction($composerJsonService);

    $projectRoot = new FileSetupEntity($projectDir, $filePathToClassAction->execute($projectDir));
    $exportRoot = new FileSetupEntity($exportDir, $filePathToClassAction->execute($exportDir));
    $setup = new ProjectSetupEntity($projectRoot, $exportRoot);

    $phpFileFactory = new PhpFileFactory();
    $writePhpFileAction = new WritePhpFileAction();

    $reflectionClassToFileSetupEntity = new ReflectionClassToFileSetupEntity($setup);
    $expectationObjectEntityFactory = new ExpectationObjectEntityFactory($phpFileFactory);
    $assertObjectEntityFactory = new AssertObjectEntityFactory($reflectionClassToFileSetupEntity, $phpFileFactory);

    $phpDocEntityFactory = new PhpDocEntityFactory(new PhpDocStringResolver(new Lexer(), new PhpDocParser(new TypeParser(), new ConstExprParser())));
    $expectationFileContentAction = new ExpectationFileContentAction($expectationObjectEntityFactory, $writePhpFileAction);

    $removeAssertFileAction = new RemoveAssertFileAction();

    $generateAssertMethodAction = new GenerateAssertMethodAction(new TestFrameworkService());
    $assertFileStateEntityFactory = new AssertFileStateEntityFactory($assertObjectEntityFactory);
    $generateAssertClass = new GenerateAssertClassAction(
        $removeAssertFileAction,
        $assertFileStateEntityFactory,
        $phpDocEntityFactory,
        $expectationFileContentAction,
        $writePhpFileAction,
        $generateAssertMethodAction
    );

    $reflectionClassFactory = new ReflectionClassFactory($setup, $filePathToClassAction);
}

foreach ($files as $file) {
    $results = $generateAssertClass->execute(
        $reflectionClassFactory->create($file)
    );

    foreach ($results as $source) {
        echo sprintf('Class %s as file %s%s', $source->class, $source->pathname, PHP_EOL);
    }
}
