<?php

declare(strict_types=1);

namespace Tests\DriveTester\Command;

use Tester\Assert;
use Nette\DI\Container;
use App\Command\DriveDetectorCommand;
use App\Command\DriveDiscoveryCommand;
use App\Process\MockProcessFactory;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * Drive discovery command test.
 */
class DriveDiscoveryCommandTest extends \Tester\TestCase
{
    /** @var DriveDiscoveryCommand Drive discovery command*/
    private $driveDiscoveryCmd;

    /** @var MockProcessFactory Process factory */
    private $processFactory;

    /**
     * Class constructor.
     *
     * @param Containter $containter Nette DI container
     */
    public function __construct(Container $container)
    {
        $this->driveDiscoveryCmd = $container->getByType('App\Command\DriveDiscoveryCommand');
        $this->processFactory = $container->getService('ProcessFactory');
    }

    /**
     * Detect all drives test.
     *
     * @dataProvider DriveDiscoveryCommand_DetectDrives.ini
     *
     * @param string $output Command output
     * @param array $drives Detected drives
     * @return void
     */
    public function testDetectDrives(string $output, array $drives = []): void
    {
        $this->processFactory->addCommand(['/bin/echo', $output]);
        Assert::same($drives, $this->driveDiscoveryCmd->detectDrives());
    }

    /**
     * Detect system drive test.
     *
     * @dataProvider DriveDiscoveryCommand_DetectSystemDrive.ini
     *
     * @param string $output Command output
     * @param array $drives Detected system drives
     * @return void
     */
    public function testDetectSystemDrive(string $output, array $drives = []): void
    {
        $this->processFactory->addCommand(['/bin/echo', $output]);
        Assert::equal($drives, $this->driveDiscoveryCmd->detectSystemDrives());
    }

    /**
     * Detect system drives test.
     *
     * @return void
     */
    public function testDetectSystemDrives(): void
    {
        $this->processFactory->addCommand(['/bin/echo', "Filesystem      Size  Used Avail Use% Mounted on
        udev             32G     0   32G   0% /dev
        tmpfs           6.3G  9.1M  6.3G   1% /run
        /dev/md0        9.3G  2.1G  7.3G  22% /
        tmpfs            32G     0   32G   0% /dev/shm
        tmpfs           5.0M     0  5.0M   0% /run/lock
        tmpfs            32G     0   32G   0% /sys/fs/cgroup
        tmpfs            32G   48K   32G   1% /var/lib/ceph/osd/ceph-0"]);
        $this->processFactory->addCommand(['/bin/echo', "/dev/md0:
        Version : 1.2
  Creation Time : Fri Feb  5 17:24:26 2016
     Raid Level : raid1
     Array Size : 9756672 (9.30 GiB 9.99 GB)
  Used Dev Size : 9756672 (9.30 GiB 9.99 GB)
   Raid Devices : 2
  Total Devices : 2
    Persistence : Superblock is persistent

    Update Time : Thu Nov 14 13:25:02 2019
          State : clean
 Active Devices : 2
Working Devices : 2
 Failed Devices : 0
  Spare Devices : 0

           Name : storage3:0
           UUID : e3d06f8f:18a2dc1d:0a7dbf8a:11051460
         Events : 189847

    Number   Major   Minor   RaidDevice State
       2       8      145        0      active sync   /dev/sdj1
       3       8      161        1      active sync   /dev/sdk1"]);
        Assert::equal(['/dev/sdj', '/dev/sdk'], $this->driveDiscoveryCmd->detectSystemDrives());
    }

    /**
     * Command exit code test.
     *
     * @throws \App\Process\ProcessFailedException
     * @return void
     */
    public function testCommandExitCode(): void
    {
        $this->processFactory->addCommand(['/bin/bash', '-c', 'exit 1']);
        $this->driveDiscoveryCmd->detectSystemDrives();
    }

    /**
     * Command not found test.
     *
     * @throws \App\Process\ProcessFailedException
     * @return void
     */
    public function testCommandNotFound(): void
    {
        $this->processFactory->addCommand(['dddd']);
        $this->driveDiscoveryCmd->detectSystemDrives();
    }
}

$test = new DriveDiscoveryCommandTest($container);
$test->run();
