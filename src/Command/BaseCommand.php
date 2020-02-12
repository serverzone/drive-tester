<?php declare(strict_types=1);

namespace App\Command;

use App\Process\IProcessFactory;
use App\Process\Process;
use App\Process\ProcessFailedException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessSignaledException;

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
        if ($this->process == null) {
            throw new RuntimeException('No process has been executed yet');
        }

        return $this->process;
    }

    /**
     * Run external command and return his output.
     *
     * @param array $command Command
     * @param int $timeout Command timeout
     * @param array $options Options
     * @return mixed
     */
    protected function runCommand(array $command, int $timeout = 120, array $options = [])
    {
        $this->runProcess($command, $timeout);
        if ($this->isSuccessful() === false) {
            throw new ProcessFailedException($this->process);
        }

        return $this->processResult($options);
    }

    /**
     * Checks if the process ended succesfully.
     *
     * @return boolean
     */
    protected function isSuccessful(): bool
    {
        if ($this->process == null) {
            throw new RuntimeException('No process has been executed yet');
        }

        return $this->process->isSuccessful();
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
        return $this->process->getOutput();
    }
}
