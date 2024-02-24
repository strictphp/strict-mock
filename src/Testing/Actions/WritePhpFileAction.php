<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Actions;

final class WritePhpFileAction
{

    public function execute(string $directory, string $className, string $content): string
    {
        $filePath = $directory . DIRECTORY_SEPARATOR . $className . '.php';
        file_put_contents($filePath, $content);

        return $filePath;
    }
}
