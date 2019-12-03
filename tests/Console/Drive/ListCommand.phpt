<?php

declare(strict_types=1);

namespace Tests\Console\Drive;

use App\Command\DriveDiscoveryCommand;
use App\Command\GetSerialNumberCommand;
use App\Console\Drive\ListCommand;
use Mockery;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Drive list console command test.
 */
class ListCommandTest extends \Tester\TestCase
{
    /**
     * Run command test.
     */
    public function testRun(): void
    {
        $driveDiscoveryCmd = Mockery::mock(DriveDiscoveryCommand::class, [
            'detectDrives' => ['/dev/sda', '/dev/sdb'],
            'detectSystemDrives' => ['/dev/sda'],
        ]);
        $getSerialNumberCmd = Mockery::mock(GetSerialNumberCommand::class, [
            'getSerialNumber' => 'serial number',
        ]);

        $cmd = new ListCommand($driveDiscoveryCmd, $getSerialNumberCmd);
        Assert::same(0, $cmd->run(new StringInput(''), new ConsoleOutput()));
    }
}

$test = new ListCommandTest($container);
$test->run();
