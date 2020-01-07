<?php

declare(strict_types=1);

namespace Tests\Console\Drive;

use App\Command\DriveDiscoveryCommand;
use App\Command\GetSerialNumberCommand;
use App\Console\Drive\ListCommand;
use Mockery;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
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
        $drives = ['/dev/sda', '/dev/sdb', '/dev/sdc'];
        $systemDrives = ['/dev/sda'];

        $driveDiscoveryCmd = Mockery::mock(DriveDiscoveryCommand::class, [
            'detectDrives' => $drives,
            'detectSystemDrives' => $systemDrives,
        ]);
        $getSerialNumberCmd = Mockery::mock(GetSerialNumberCommand::class, [
            'getSerialNumber' => 'serial number',
        ]);

        $cmd = new ListCommand($driveDiscoveryCmd, $getSerialNumberCmd);
        $output = new BufferedOutput();
        Assert::same(0, $cmd->run(new StringInput(''), $output));

        $content = $output->fetch();
        $sumaryStr = sprintf(ListCommand::SUMMATY_FORMAT, count($drives), count($systemDrives), count($drives) - count($systemDrives));
        Assert::match('#' . $sumaryStr . '#', $content);
    }
}

$test = new ListCommandTest($container);
$test->run();
