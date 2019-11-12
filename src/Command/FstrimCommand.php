<?php

declare(strict_types=1);

namespace App\Command;

/**
 * Fstrim command.
 */
class FstrimCommand extends BaseCommand
{

    /**
     * Discard unused blocks on a mounted filesystem.
     *
     * @param string $path Path to device (e.g. /dev/sdb)
     * @param array $eventOptions Event options
     * @return void
     */
    public function execute(string $path, array $eventOptions = []): void
    {
        $this->runCommand(['/sbin/fstrim', $path], 3600, true, $eventOptions);
    }
}
