<?php

declare(strict_types=1);

namespace App\Command;

/**
 * Mdadm command.
 */
class MdadmCommand extends BaseCommand
{
    /**
     * Return mdadm query detail.
     *
     * @param string $path Drive path (e.g. '/dev/sdb')
     * @return string
     */
    public function queryDetail(string $path): string
    {
        return $this->runCommand(['/sbin/mdadm',  '--query', '--detail', $path]);
    }
}
