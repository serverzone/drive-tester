<?php

declare(strict_types=1);

namespace App\Command;

use App\Process\IProcessFactory;
use Psr\Log\LoggerInterface;
use App\Process\Process;
use App\Process\ProcessFailedException;

/**
 * Abstract base command.
 */
abstract class BaseCommand
{
    /** @var IProcessFactory Process factory */
    protected $processFactory;

    /** @var LoggerInterface Logger */
    protected $logger;

    /** @var Process Process */
    protected $process;

    /**
     * Class constructor.
     *
     * @param IProcessFactory $processFactory Process factory
     * @param LoggerInterface $logger Logger
     */
    public function __construct(IProcessFactory $processFactory, LoggerInterface $logger)
    {
        $this->processFactory = $processFactory;
        $this->logger = $logger;
    }

    /**
     * Return process.
     *
     * @return Process
     */
    public function getProcess(): Process
    {
        return $this->process;
    }

    /**
     * Run external command and return his output.
     *
     * @param array $command Command
     * @param int $timeout Command timeout
     * @param bool $mustRun Must run flag
     * @param array $options Options
     * @return mixed
     */
    protected function runCommand(array $command, int $timeout = 120, bool $mustRun = true, array $options = [])
    {
        $this->runProcess($command, $timeout);

        if ($mustRun && $this->process->isSuccessful() === false) {
            throw new ProcessFailedException($this->process);
        }

        return $this->processResult($options);
    }

    /**
     * Run command process.
     *
     * @param array $command Command
     * @param integer $timeout Run timeout
     * @return void
     */
    protected function runProcess(array $command, int $timeout = 120): void
    {
        $this->logger->debug('Running command', ['command' => implode(" ", $command)]);

        // create process
        $this->process = $this->processFactory->create($command);
        $this->process->setTimeout($timeout);

        // run process
        $this->process->run();

        // log exit status
        $this->logger->info('Command exited', [
            'command' => implode(" ", $command),
            'exitCode' => $this->process->getExitCode(),
            'time' => $this->process->getRunningTime(),
        ]);
    }

    /**
     * Processing command result.
     *
     * @param array $options Options
     * @return mixed
     */
    protected function processResult(array $options = [])
    {
        return $this->process->isSuccessful() ? $this->process->getOutput() : null;
    }
}
