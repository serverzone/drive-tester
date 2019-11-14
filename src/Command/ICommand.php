<?php

declare(strict_types=1);

namespace App\Command;

use App\Process\Process;

/**
 * Command interface.
 */
interface ICommand
{

    /**
     * Return process.
     *
     * @return Process
     */
    public function getProcess(): Process;
}
