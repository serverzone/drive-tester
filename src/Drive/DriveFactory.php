<?php

declare(strict_types=1);

namespace App\Drive;

use App\Command\GetSerialNumberCommand;
use App\Command\PartedCommand;
use App\Command\SmartCtlCommand;
use App\Command\BadblocksCommand;
use App\Command\FstrimCommand;

/**
 * Drive factory interface.
 */
class DriveFactory
{
    /** @var GetSerialNumberCommand Get drive serial number command */
    protected $serialNoCmd;

    /** @var PartedCommand Parted command */
    protected $partedCmd;

    /** @var SmartCtlCommand SMARTctl command */
    protected $smartCtlCmd;

    /** @var BadblocksCommand Detect bad blocks command */
    protected $badblocksCmd;

    /** @var FstrimCommand Fstrim command  */
    protected $fstrimCmd;

    /**
     * Class constructor.
     *
     * @param GetSerialNumberCommand $serialNoCmd Get drive serial number command
     * @param PartedCommand $partedCmd Parted command
     * @param SmartCtlCommand $smartCtlCmd Smartctl command
     * @param BadblocksCommand $badblocksCmd Detect bad blocks command
     * @param FstrimCommand $fstrimCmd Fstrim command
     */
    public function __construct(GetSerialNumberCommand $serialNoCmd, PartedCommand $partedCmd, SmartCtlCommand $smartCtlCmd, BadblocksCommand $badblocksCmd, FstrimCommand $fstrimCmd)
    {
        $this->serialNoCmd = $serialNoCmd;
        $this->partedCmd = $partedCmd;
        $this->smartCtlCmd = $smartCtlCmd;
        $this->badblocksCmd = $badblocksCmd;
        $this->fstrimCmd = $fstrimCmd;
    }

    /**
     * Create Drive instance.
     *
     * @param string $path Drive path
     * @return Drive
     */
    public function create(string $path): Drive
    {
        return new Drive($path, $this->serialNoCmd, $this->partedCmd, $this->smartCtlCmd, $this->badblocksCmd, $this->fstrimCmd);
    }
}
