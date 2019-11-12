<?php

declare(strict_types=1);

namespace App\Command;

/**
 * Drive detector command.
 */
class DriveDiscoveryCommand extends BaseCommand
{

    /**
     * Detect all drives.
     *
     * @return array
     */
    public function detectDrives(): array
    {
        $process = $this->runCommand(['/sbin/fdisk', '-l']);
        preg_match_all('#Disk (/dev/sd[a-z]+):#', $process->getOutput(), $matches);

        return $matches[1];
    }

    /**
     * Detect system drive.
     *
     * @return string|null
     */
    public function detectSystemDrive(): ?string
    {
        $process = $this->runCommand(['/bin/df', '/']);
        preg_match_all('#/dev/sd[a-z]+#', $process->getOutput(), $matches);

        return $matches[0][0] ?? null;
    }
}
