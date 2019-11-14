<?php

declare(strict_types=1);

namespace App\Command;

use App\Process\IProcessFactory;
use Contributte\Utils\Strings;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @param EventDispatcherInterface $dispatcher Event dispatcher
     * @param LoggerInterface $logger Logger
     */
    public function __construct(IProcessFactory $processFactory, EventDispatcherInterface $dispatcher, LoggerInterface $logger, MdadmCommand $mdadmCmd)
    {
        parent::__construct($processFactory, $dispatcher, $logger);
        $this->mdadmCmd = $mdadmCmd;
    }

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
     * Detect system drive(s).
     *
     * @return array
     */
    public function detectSystemDrives(): array
    {
        $process = $this->runCommand(['/bin/df', '/']);
        preg_match_all('#/dev/(sd[a-z]+|md[0-9])#', $process->getOutput(), $matches);
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
