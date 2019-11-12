<?php

declare(strict_types=1);

namespace App\Process;

/**
 * Process factory interface.
 */
interface IProcessFactory
{
    /**
     * Create new process.
     *
     * @param array $command Command
     * @return Process
     */
    public function create(array $command): Process;
}
