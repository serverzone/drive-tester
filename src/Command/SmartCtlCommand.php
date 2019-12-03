<?php

declare(strict_types=1);

namespace App\Command;

use RuntimeException;

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
     * @return string|null
     */
    public function getInfo(string $path, array $eventOptions = []): ?string
    {
        return $this->runCommand(['/usr/sbin/smartctl', '--all', $path], 120, $eventOptions);
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

        return ($this->process->getExitCode() & 0x1) === 0;
    }
}
