<?php declare(strict_types=1);

namespace App\Checker;

use App\Drive\DriveException;
use App\Drive\DriveFactory;
use App\Event\CheckerEvent;
use App\Locker\FileLockFactory;
use App\Process\ProcessFailedException;
use Contributte\Utils\DateTime;
use Jenner\SimpleFork\Runnable;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\Exception\ProcessSignaledException;

/**
 * Drive checker.
 */
class Checker implements Runnable
{
    /** @var string Drive path */
    protected $path;

    /** @var SharedStatusCache Shared status cache */
    protected $cache;

    /** @var DriveFactory Drive factory */
    protected $driveFactory;

    /** @var EventDispatcherInterface Event dispatcher */
    protected $dispatcher;

    /** @var bool Enable write test for ssd flag */
    private $ssdWriteTestEnabled;

    /**
     * Undocumented function
     *
     * @param string $path Device path
     * @param bool $ssdWriteTestEnabled Enable write test for ssd flag
     * @param DriveFactory $driveFactory Drive factory
     * @param SharedStatusCache $cache Shared status cache
     * @param EventDispatcherInterface $dispatcher Event dispatcher
     */
    public function __construct(string $path, bool $ssdWriteTestEnabled, DriveFactory $driveFactory, SharedStatusCache $cache, EventDispatcherInterface $dispatcher)
    {
        $this->path = $path;
        $this->ssdWriteTestEnabled = $ssdWriteTestEnabled;
        $this->cache = $cache;
        $this->driveFactory = $driveFactory;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Run checker.
     *
     * @return void
     * @throws \Exception
     */
    public function run(): void
    {
        $status = new Status();

        $locker = (new FileLockFactory())->create($this->path);
        if ($locker->acquire(false) === false) {
            $this->updateStatus($status, 'Already running', Status::STATE_DONE);
            return;
        }

        try {
            $drive = $this->driveFactory->create($this->path);
            $driveSerialNumber = $drive->getSerialNumber();
            if ($driveSerialNumber === null) {
                $this->updateStatus($status, 'No serial number', Status::STATE_ERROR);
                return;
            }
            $status->setSerialNumber($driveSerialNumber);
            $isSsd = $drive->isSsd();

            $options = [
                'startedAt' => new DateTime(),
                'serialNumber' => $driveSerialNumber,
            ];

            $this->updateStatus($status, 'Getting parted info');
            $drive->getPartedInfo(array_merge($options, ['label' => 'parted']));

            $this->updateStatus($status, 'Storing smartctl info');
            $drive->getSmartctlInfo(array_merge($options, ['label' => 'smartctl']));

            $this->updateStatus($status, 'Checking bad blocks');
            $writeMode = $isSsd ? $this->ssdWriteTestEnabled : true;
            $badBlocksCount = $drive->badblocks($writeMode, array_merge($options, ['label' => 'badblocks']));
            if ($badBlocksCount != 0) {
                $this->updateStatus($status, sprintf('%d bad blocks found', $badBlocksCount), Status::STATE_ERROR);
            }

            $this->updateStatus($status, 'Storing smartctl info');
            $drive->getSmartctlInfo(array_merge($options, ['label' => 'smartctl.badblocks']));

            if ($isSsd) {
                $this->updateStatus($status, 'Running fstrim');
                $drive->fstrim(array_merge($options, ['label' => 'fstrim']));
                $this->updateStatus($status, 'Storing smartctl info');
                $drive->getSmartctlInfo(array_merge($options, ['label' => 'smartctl.fstrim']));
            }

            $this->updateStatus($status, 'OK', Status::STATE_DONE);
        } catch (ProcessFailedException | DriveException | ProcessSignaledException $ex) {
            $this->updateStatus($status, $ex->getMessage(), Status::STATE_ERROR);
        }

        $locker->release();

        $this->dispatcher->dispatch(new CheckerEvent($this->path, $status));
    }

    /**
     * Set message to status.
     *
     * @param Status $status Status
     * @param string $message Message
     * @param int $state State
     * @return void
     */
    protected function updateStatus(Status &$status, string $message, int $state = Status::STATE_RUNNING)
    {
        $status->updateState($state, $message);
        $this->cache->setStatus($this->path, $status);
    }
}
