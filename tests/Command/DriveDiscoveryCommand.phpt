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
     * @dataProvider DriveDiscoveryCommand_DetectSystemDrives.ini
     *
     * @param string $fdisk Fdisk output
     * @param string $mdadm Mdadm output
     * @param string[] $drives Detected system drives
     * @return void
     */
    public function testDetectSystemDrives(string $fdisk, string $mdadm, array $drives): void
    {
        $this->processFactory->addCommand(['/bin/echo', $fdisk]);
        $this->processFactory->addCommand(['/bin/echo', $mdadm]);
        Assert::equal($drives, $this->driveDiscoveryCmd->detectSystemDrives());
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
