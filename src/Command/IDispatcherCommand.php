<?php

declare(strict_types=1);

namespace App\Command;

use App\Process\Process;

/**
 * Command interface.
 */
interface IDispatcherCommand
{

    /**
     * Return process.
     *
     * @return Process
     */
    public function getProcess(): Process;

    /**
     * Return command result.
     *
     * @return mixed
     */
    public function getResult();
}
