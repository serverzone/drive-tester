<?php

declare(strict_types=1);

namespace App\Command;

/**
 * Bad blocks command.
 */
class BadblocksCommand extends DispatchedCommand
{
    /**
     * Detect bad blocks on drive.
     *
     * @param string $path Drive path (e.g. '/dev/sdb')
     * @param array $eventOptions Event options
     * @return integer
     */
    public function detect(string $path, array $eventOptions = []): int
    {
        return $this->runCommand(['/sbin/badblocks',  '-wve150', '-b8192', $path], 10 * 24 * 3600, $eventOptions);
    }

    /**
     * Processing command result.
     *
     * @param array $options Options
     * @return mixed
     */
    protected function processResult(array $options = [])
    {
        preg_match_all('#Pass completed, ([0-9]*) bad blocks found.#', $this->process->getOutput() . $this->process->getErrorOutput(), $matches);
        if (isset($matches[1][0])) {
            return intval($matches[1][0]);
        }

        return -1;
    }
}
