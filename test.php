<?php declare(strict_types=1);

use LaraStrict\StrictMock\Testing\Actions\FilePathToClassAction;
use LaraStrict\StrictMock\Testing\Actions\WritePhpFileAction;
use LaraStrict\StrictMock\Testing\Assert\Actions\GenerateAssertClassAction;
use LaraStrict\StrictMock\Testing\Entities\FileSetupEntity;
use LaraStrict\StrictMock\Testing\Entities\ProjectSetupEntity;
use LaraStrict\StrictMock\Testing\Expectation\Actions\ExpectationFileContentAction;
use LaraStrict\StrictMock\Testing\Factories\AssertFileStateEntityFactory;
use LaraStrict\StrictMock\Testing\Factories\PhpDocEntityFactory;
use LaraStrict\StrictMock\Testing\Factories\PhpFileFactory;
use LaraStrict\StrictMock\Testing\Factories\ReflectionClassFactory;
use LaraStrict\StrictMock\Testing\Helpers\Realpath;
use LaraStrict\StrictMock\Testing\Services\ComposerJsonService;
use LaraStrict\StrictMock\Testing\Transformers\ReflectionClassToFileSetupEntity;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TypeParser;

require __DIR__ . '/vendor/autoload.php';

$file = 'src/Testing/Contracts/FindAllClassesActionContract.php';

{ // config
    $projectRoot = new FileSetupEntity(RealPath::make(__DIR__), 'LaraStrict\StrictMock\Testing');
    $exportRoot = new FileSetupEntity(RealPath::make(__DIR__), 'LaraStrict\StrictMock\Tests');

    $setup = new ProjectSetupEntity($projectRoot, $exportRoot);

    $phpFileFactory = new PhpFileFactory();
    $writePhpFileAction = new WritePhpFileAction();

    $phpDocEntityFactory = new PhpDocEntityFactory(new PhpDocStringResolver(new Lexer(), new PhpDocParser(new TypeParser(), new ConstExprParser())));
    $expectationFileContentAction = new ExpectationFileContentAction($phpFileFactory, $writePhpFileAction);

    $namespaceAction = new ReflectionClassToFileSetupEntity($setup);
    $assertFileStateEntityFactory = new AssertFileStateEntityFactory($phpFileFactory, $namespaceAction);
    $generateAssertClass = new GenerateAssertClassAction($assertFileStateEntityFactory, $phpDocEntityFactory, $expectationFileContentAction, $writePhpFileAction);

    $composerJsonService = new ComposerJsonService();
    $filePathToClassAction = new FilePathToClassAction($composerJsonService);

    $reflectionClassFactory = new ReflectionClassFactory($setup, $filePathToClassAction);
}

$generateAssertClass->execute(
    $reflectionClassFactory->create($file)
);
