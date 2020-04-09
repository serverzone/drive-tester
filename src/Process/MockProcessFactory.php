<?php

declare(strict_types=1);

namespace App\Process;

/**
 * Mock process factory for testing.
 */
class MockProcessFactory implements IProcessFactory
{
    /** @var array Mocked commands */
    private $commands = [];

    /** @var array  Original command */
    private $originalCommand = [];

    /**
     * Create new process.
     *
     * @param array $command Command
     * @return Process
     */
    public function create(array $command): Process
    {
        $this->originalCommand = $command;

        return new Process(array_shift($this->commands));
    }

    /**
     * Add command.
     *
     * @param array $command
     * @return void
     */
    public function addCommand(array $command): void
    {
        $this->commands[] = $command;
    }

    /**
     * Return original command.
     *
     * @return array
     */
    public function getOriginalCommand(): array
    {
        return $this->originalCommand;
    }
}
