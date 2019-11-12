<?php

declare(strict_types=1);

namespace App\Locker;

use Contributte\Utils\Strings;
use Jenner\SimpleFork\Lock\FileLock;

/**
 * Locker factory.
 */
class FileLockFactory
{
    /**
     * Create File lock.
     *
     * @param string $path Drive path (e.g. /dev/sda)
     * @return FileLock
     */
    public function create(string $path): FileLock
    {
        $lockFile = sys_get_temp_dir() . '/drive_tester' . Strings::replace($path, '#\/#', '_') . '.lock';
        if (file_exists($lockFile) === false) {
            file_put_contents($lockFile, '');
        }

        return FileLock::create($lockFile);
    }
}
