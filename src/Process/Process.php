<?php declare(strict_types=1);

namespace App\Process;

use Contributte\Utils\Strings;
use Symfony\Component\Process\Process as SymfonyProcess;

/**
 * Process.
 */
class Process extends SymfonyProcess
{
    /** @var float Process running time */
    private $runningTime;

    public function run(callable $callback = null, array $env = []): int
    {
        $start = microtime(true);
        $result = parent::run($callback, $env);
        $this->runningTime = microtime(true) - $start;

        return $result;
    }

    /**
     * Return process running time.
     *
     * @return float|null
     */
    public function getRunningTime(): ?float
    {
        return $this->runningTime;
    }

    /**
     * Return process output.
     *
     * @return string
     */
    public function getOutput(): string
    {
        return Strings::normalize(parent::getOutput());
    }

    /**
     * Return process error output.
     *
     * @return string
     */
    public function getErrorOutput(): string
    {
        return Strings::normalize(parent::getErrorOutput());
    }
}
