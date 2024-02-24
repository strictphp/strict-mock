<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Laravel\Commands;

use Illuminate\Console\Command;
use LaraStrict\StrictMock\Testing\Actions\InputArgumentClassToClassesAction;
use LaraStrict\StrictMock\Testing\Assert\Actions\GenerateAssertClassAction;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:expectation', description: 'Make expectation class for given class')]
class MakeExpectationCommand extends Command
{

    protected $signature = 'make:expectation
        {class : Class name of path to class using PSR-4 specs}
    ';


    public function handle(
        GenerateAssertClassAction $generateAssertClassAction,
        InputArgumentClassToClassesAction $inputArgumentClassToClassesAction,
    ): int
    {
        foreach ($inputArgumentClassToClassesAction->execute($this->input->getArgument('class')) as $classReflecton) {
            $done = $generateAssertClassAction->execute($classReflecton);
            foreach ($done as $class => $file) {
                $this->writeFile($class, $file);
            }
        }

        return 0;
    }


    protected function writeFile(
        string $className,
        string $fileName,
    ): void
    {
        $successMessage = 'File generated [' . $className . ']';
        if (property_exists($this, 'components')) {
            $this->components->info($successMessage);
        } else {
            $this->info($successMessage);
        }

        $this->line(sprintf('  <fg=gray>File written to [%s]</>', $fileName));
        $this->newLine();
    }

}
