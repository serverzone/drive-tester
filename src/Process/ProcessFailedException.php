<?php

declare(strict_types=1);

namespace App\Process;

use Symfony\Component\Process\Process;

/**
 * Exception for failed processes.
 */
class ProcessFailedException extends \RuntimeException
{
    /** @var Process Failed process */
    private $process;

    /**
     * Class constructor.
     *
     * @param Process $process Failed process
     */
    public function __construct(Process $process)
    {
        if ($process->isSuccessful()) {
            throw new \InvalidArgumentException('Expected a failed process, but the given process was successful.');
        }

        $error = sprintf(
            "The command '%s' failed with exit code %s (%s)",
            strtok($process->getCommandLine(), "'"),
            $process->getExitCode(),
            $process->getExitCodeText()
        );

        $this->process = $process;
        parent::__construct($error);
    }

    /**
     * Return failed process.
     *
     * @return Process
     */
    public function getProcess(): Process
    {
        return $this->process;
    }
}
