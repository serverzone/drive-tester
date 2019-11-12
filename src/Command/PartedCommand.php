<?php

declare(strict_types=1);

namespace App\Command;

/**
 * Parted command.
 */
class PartedCommand extends BaseCommand
{

    /**
     * Return drive partition information.
     *
     * @param string $devicePath Device path (e.g. /dev/sdb)
     * @param array $eventOptions Event options
     * @return string
     */
    public function print(string $devicePath, array $eventOptions = []): string
    {
        $process = $this->runCommand(['/sbin/parted', '--machine', $devicePath, 'print'], 120, true, $eventOptions);

        return $process->getOutput();
    }
}
