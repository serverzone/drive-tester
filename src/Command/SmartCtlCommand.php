<?php

declare(strict_types=1);

namespace App\Command;

use App\Process\ProcessFailedException;

/**
 * Get SMART ctl command.
 */
class SmartCtlCommand extends BaseCommand
{

    /**
     * Return SMARTctl info.
     *
     * @param string $path Drive path
     * @param array $eventOptions Event options
     * @return string
     */
    public function getInfo(string $path, array $eventOptions = []): string
    {
        $process = $this->runCommand(['/usr/sbin/smartctl', '--all', $path], 120, false, $eventOptions);

        if (($process->getExitCode() & 0x1) !== 0) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }
}
