<?php

declare(strict_types=1);

namespace App\Command;

use App\Process\IProcessFactory;
use Psr\Log\LoggerInterface;
use App\Event\CommandEvent;
use App\Process\Process;
use App\Process\ProcessFailedException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Abstract base command.
 */
abstract class BaseCommand
{

    /** @var IProcessFactory Process factory */
    protected $processFactory;

    /** @var EventDispatcherInterface Event dispatcher */
    protected $dispatcher;

    /** @var LoggerInterface Logger */
    protected $logger;

    /**
     * Class constructor.
     *
     * @param IProcessFactory $processFactory Process factory
     * @param EventDispatcherInterface $dispatcher Event dispatcher
     * @param LoggerInterface $logger Logger
     */
    public function __construct(IProcessFactory $processFactory, EventDispatcherInterface $dispatcher, LoggerInterface $logger)
    {
        $this->processFactory = $processFactory;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    /**
     * Run external command and return his output.
     *
     * @param array $command Command
     * @param int $timeout Command timeout
     * @param bool $mustRun Must run flag
     * @param array $eventOptions Event options
     * @return Process
     */
    protected function runCommand(array $command, int $timeout = 120, bool $mustRun = true, array $eventOptions = []): Process
    {
        $this->logger->debug('Running command', ['command' => implode(" ", $command)]);

        // create process
        $process = $this->processFactory->create($command);
        $process->setTimeout($timeout);

        // run process
        $process->run();

        // log exit status
        $this->logger->info('Command exited', [
            'command' => implode(" ", $command),
            'exitCode' => $process->getExitCode(),
            'time' => $process->getRunningTime(),
        ]);

        // send event
        $this->dispatcher->dispatch(new CommandEvent($process, $eventOptions));

        // check must run option
        if ($mustRun && $process->isSuccessful() === false) {
            throw new ProcessFailedException($process);
        }

        return $process;
    }
}
