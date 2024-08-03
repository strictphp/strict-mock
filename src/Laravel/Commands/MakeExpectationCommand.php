<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Laravel\Commands;

use Illuminate\Console\Command;
use StrictPhp\StrictMock\Testing\Actions\InputArgumentClassToClassesAction;
use StrictPhp\StrictMock\Testing\Assert\Actions\GenerateAssertClassAction;
use StrictPhp\StrictMock\Testing\Exceptions\IgnoreAssertException;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:expectation', description: 'Make expectation class for given class')]
class MakeExpectationCommand extends Command
{
    protected $signature = 'make:expectation
        {class* : Class name of path to class using PSR-4 specs}
    ';

    public function handle(
        GenerateAssertClassAction $generateAssertClassAction,
        InputArgumentClassToClassesAction $inputArgumentClassToClassesAction,
    ): int {
        $class = $this->input->getArgument('class');

        $classes = $inputArgumentClassToClassesAction->execute($class);

        foreach ($classes as $classReflection) {
            try {
                $done = $generateAssertClassAction->execute($classReflection);
                foreach ($done as $file) {
                    $this->writeFile($file->class, $file->pathname);
                }
            } catch (IgnoreAssertException $e) {
                $this->info(sprintf('Class is ignored "%s".', $e->getMessage()));
            }
        }

        return 0;
    }

    protected function writeFile(string $className, string $fileName): void {
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
