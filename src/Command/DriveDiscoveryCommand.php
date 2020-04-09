<?php

declare(strict_types=1);

namespace App\Command;

use App\Process\IProcessFactory;
use Contributte\Utils\Strings;
use DirectoryIterator;
use Psr\Log\LoggerInterface;

/**
 * Drive detector command.
 */
class DriveDiscoveryCommand extends BaseCommand
{
    /** @var MdadmCommand Mdadm command */
    private $mdadmCmd;

    /**
     * Class constructor.
     *
     * @param IProcessFactory $processFactory Process factory
     * @param LoggerInterface $logger Logger
     * @param MdadmCommand $mdadmCmd Mdadm command
     */
    public function __construct(IProcessFactory $processFactory, LoggerInterface $logger, MdadmCommand $mdadmCmd)
    {
        parent::__construct($processFactory, $logger);
        $this->mdadmCmd = $mdadmCmd;
    }

    /**
     * Detect all drives.
     *
     * @return array
     */
    public function detectDrives(): array
    {
        $result = $this->runCommand(['/usr/bin/find', '/dev/', '-maxdepth', '1']);

        return array_values(preg_grep('#sd[a-z]+$#', explode(PHP_EOL, $result)));
    }

    /**
     * Detect system drive(s).
     *
     * @return array
     */
    public function detectSystemDrives(): array
    {
        $result = $this->runCommand(['/bin/df', '/']);
        preg_match_all('#/dev/(sd[a-z]+|md[0-9])#', $result, $matches);
        $drive = $matches[0][0] ?? null;

        if ($drive === null) {
            return [];
        }

        if (Strings::startsWith($drive, '/dev/md')) {
            $detail = $this->mdadmCmd->queryDetail($drive);
            preg_match_all('#/dev/sd[a-z]+#', $detail, $matches);

            return $matches[0];
        }

        return [$drive];
    }
}
