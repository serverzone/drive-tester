<?php

declare(strict_types=1);

namespace Tests\Drive;

use Tester\Assert;
use Nette\DI\Container;
use App\Drive\Drive;
use Mockery;
use App\Command\BadblocksCommand;
use App\Command\FstrimCommand;
use App\Command\GetSerialNumberCommand;
use App\Command\PartedCommand;
use App\Command\SmartCtlCommand;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * Drive test.
 */
class DriveTest extends \Tester\TestCase
{
    /** @var MockProcessFactory Process factory */
    private $processFactory;

    /**
     * Class constructor.
     *
     * @param Containter $containter Nette DI container
     */
    public function __construct(Container $container)
    {
        $this->processFactory = $container->getService('ProcessFactory');
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * Detect bad blocks test.
     *
     * @return void
     */
    public function testDetectBadBlocks(): void
    {
        $badBlocks = rand(0, 1024);
        $output = sprintf('Pass completed, %d bad blocks found. (0/%d/0 errors)', $badBlocks, $badBlocks);
        $this->processFactory->addCommand(['/bin/echo', $output]);
        $getSerialNumberCmd = Mockery::mock(GetSerialNumberCommand::class, ['getSerialNumber' => 'serialNo']);
        $badblocksCmd = Mockery::mock(BadblocksCommand::class, ['detect' => $badBlocks]);

        $drive = new Drive('/dev/sda', $getSerialNumberCmd, Mockery::mock(PartedCommand::class), Mockery::mock(SmartCtlCommand::class), $badblocksCmd);
        Assert::same($badBlocks, $drive->badblocks());
    }
}

$test = new DriveTest($container);
$test->run();
