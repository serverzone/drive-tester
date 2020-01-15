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
 * Abstract dispatcher command.
 */
abstract class DispatchedCommand extends BaseCommand implements IDispatcherCommand
{
    /** @var mixed Command result */
    protected $result;

    /** @var EventDispatcherInterface Event dispatcher */
    protected $dispatcher;

    /**
     * Class constructor.
     *
     * @param IProcessFactory $processFactory Process factory
     * @param LoggerInterface $logger Logger
     * @param EventDispatcherInterface $dispatcher Event dispatcher
     */
    public function __construct(IProcessFactory $processFactory, LoggerInterface $logger, EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        parent::__construct($processFactory, $logger);
    }

    /**
     * Return command result.
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
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

        if ($this->isSuccessful()) {
            $throwException = false;
            $this->result = $this->processResult($options);
        } else {
            $throwException = true;
        }

        $this->dispatcher->dispatch(new CommandEvent($this, $options));

        if ($throwException) {
            throw new ProcessFailedException($this->process);
        }

        return $this->result;
    }
}
