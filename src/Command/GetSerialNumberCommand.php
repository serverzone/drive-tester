<?php

declare(strict_types=1);

namespace App\Command;

/**
 * Get drive serial number command.
 */
class GetSerialNumberCommand extends BaseCommand
{

    /**
     * Detect drive serial number.
     *
     * @param string $devPath Path to device (e.g. /dev/sdb)
     * @return string|null
     */
    public function getSerialNumber(string $devPath): ?string
    {
        $process = $this->runCommand(['/usr/bin/sdparm', '--page=sn', $devPath]);
        $lines = explode(PHP_EOL, $process->getOutput());

        return isset($lines[2]) ? trim($lines[2]) : null;
    }
}
