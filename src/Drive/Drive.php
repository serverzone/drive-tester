<?php declare(strict_types=1);

namespace App\Drive;

use App\Command\BadblocksCommand;
use App\Command\FstrimCommand;
use App\Command\GetSerialNumberCommand;
use App\Command\PartedCommand;
use App\Command\SmartCtlCommand;
use Contributte\Utils\Strings;

/**
 * Drive
 */
class Drive
{
    /** @var string Drive path */
    private $path;

    /** @var string|null Disk serialnumber */
    private $serialNo;

    /** @var int File counter */
    private $fileNo = 1;

    /** @var GetSerialNumberCommand Get drive serial number command */
    protected $serialNoCmd;

    /** @var PartedCommand Parted command */
    protected $partedCmd;

    /** @var SmartCtlCommand SMARTctl command */
    protected $smartCtlCmd;

    /** @var BadblocksCommand Bad blocks command */
    protected $badblocksCmd;

    /**
     * Return if it is rotate drive.
     *
     * @return boolean
     * @throws DriveException
     */
    public static function isRotate(string $path): bool
    {
        $rotation = @file_get_contents(sprintf('/sys/block/%s/queue/rotational', Strings::replacePrefix($path, '/dev/', '')));
        if ($rotation === false) {
            throw new DriveException('Drive not found');
        }

        return Strings::startsWith($rotation, '1') ? true : false;
    }

    /**
     * Class constructor.
     *
     * @param string $path Device path
     * @param GetSerialNumberCommand $serialNoCmd Get drive serial number command
     * @param PartedCommand $partedCmd Parted command
     * @param SmartCtlCommand $smartCtlCmd Smartctl command
     * @param BadblocksCommand $badblocksCmd Detect bad blocks command
     * @throws DriveException
     */
    public function __construct(string $path, GetSerialNumberCommand $serialNoCmd, PartedCommand $partedCmd, SmartCtlCommand $smartCtlCmd, BadblocksCommand $badblocksCmd)
    {
        $this->path = $path;
        $this->serialNoCmd = $serialNoCmd;
        $this->partedCmd = $partedCmd;
        $this->smartCtlCmd = $smartCtlCmd;
        $this->badblocksCmd = $badblocksCmd;

        $this->serialNo = $this->serialNoCmd->getSerialNumber($path);
        if ($this->serialNo === null) {
            throw new DriveException("Unable to detect serial number");
        }
    }

    /**
     * Return drive path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get parted info.
     *
     * @param array $eventOptions Event options
     * @return string
     */
    public function getPartedInfo(array $eventOptions = []): string
    {
        return $this->partedCmd->print($this->path, $eventOptions);
    }

    /**
     * Store smartctl info to file.
     *
     * @param array $eventOptions Event options
     * @return string|null
     */
    public function getSmartctlInfo(array $eventOptions = []): ?string
    {
        return $this->smartCtlCmd->getInfo($this->path, $eventOptions);
    }

    /**
     * Detect bad block on drive.
     *
     * @param bool $writeMode Enable write mode flag
     * @param array $eventOptions Event options
     * @return integer
     */
    public function badblocks(bool $writeMode = false, array $eventOptions = []): int
    {
        return $this->badblocksCmd->detect($this->path, $writeMode, $eventOptions);
    }

    /**
     * Return if drive is SSD.
     *
     * @return boolean
     * @throws DriveException
     */
    public function isSsd(): bool
    {
        return Drive::isRotate($this->path) === false ? true : false;
    }

    /**
     * Return disk serial number.
     *
     * @return string|null
     */
    public function getSerialNumber(): ?string
    {
        return $this->serialNo;
    }
}
