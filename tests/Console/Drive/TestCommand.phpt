<?php

declare(strict_types=1);

namespace Tests;

use App\Checker\Checker;
use App\Checker\CheckerFactory;
use App\Checker\Status;
use Symfony\Component\Console\Output\NullOutput;
use Tester\Assert;
use Nette\DI\Container;
use Contributte\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Mockery;
use App\Command\DriveDiscoveryCommand;
use App\Console\Drive\TestCommand;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\EventDispatcher\EventDispatcher;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Drive tester console command test.
 */
class DriveTesterCommandTest extends \Tester\TestCase
{

    /** @var Application Console application */
    private $application;

    /** @var SharedStatusCache Shared status cache */
    private $cache;

    /**
     * Class constructor.
     *
     * @param Containter $containter Nette DI container
     */
    public function __construct(Container $container)
    {
        $this->application = $container->getByType('Contributte\Console\Application');
        $this->cache = $container->getByType('App\Checker\SharedStatusCache');
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * Run command test.
     *
     * @dataProvider TestCommand.ini
     * @return void
     */
    public function testRun(array $drives, array $statusesJson, string $count, $notifyMessage): void
    {
        $statuses = [];
        foreach ($statusesJson as $key => $json) {
            $statuses[$key] = Status::fromJsonString($json);
        }

        $input = new StringInput(implode(' ', $drives));

        // prepare mockers
        $driveDiscoveryCmd = Mockery::mock(DriveDiscoveryCommand::class, [
            'detectSystemDrives' => ['/dev/sdxxx'],
        ]);
        $checkerFactory = Mockery::mock(CheckerFactory::class, [
            'create' => Mockery::mock(Checker::class, [
                'run' => null,
            ]),
        ]);
        $dispatcher = Mockery::mock(EventDispatcher::class);
        $dispatcher->shouldReceive('dispatch');

        // run command
        $cmd = new TestCommand($driveDiscoveryCmd, $checkerFactory, $this->cache, $dispatcher);
        Assert::same(intval($count), $cmd->run($input, new ConsoleOutput()));
    }

    /**
     * Run with drive auto detection test.
     *
     * @return void
     */
    public function testRunWithAutoDetection(): void
    {
        $output = new ConsoleOutput();

        // prepare mockers
        $driveDiscoveryCmd = Mockery::mock(DriveDiscoveryCommand::class, [
            'detectDrives' => ['/dev/sda', '/dev/sdb', '/dev/sdc'],
            'detectSystemDrives' => ['/dev/sda'],
        ]);
        $checkerFactory = Mockery::mock(CheckerFactory::class, [
            'create' => Mockery::mock(Checker::class, [
                'run' => null,
            ]),
        ]);
        $dispatcher = Mockery::mock(EventDispatcher::class);
        $dispatcher->shouldReceive('dispatch');

        // run command
        $cmd = new TestCommand($driveDiscoveryCmd, $checkerFactory, $this->cache, $dispatcher);
        Assert::same(0, $cmd->run(new StringInput('-a'), new ConsoleOutput()));
    }

    /**
     * Run command on system drive test.
     *
     * @return void
     */
    public function testRunCommandOnSystemDrive(): void
    {
        $drive = '/dev/sda';

        $input = new StringInput(implode(' ', [$drive]));
        $output = new ConsoleOutput();

        // prepare mockers
        $driveDiscoveryCmd = Mockery::mock(DriveDiscoveryCommand::class, [
            'detectSystemDrives' => [$drive, '/dev/sdddd'],
        ]);
        $checker = Mockery::mock(Checker::class, ['run' => null,]);
        $checkerFactory = Mockery::mock(CheckerFactory::class, ['create' => $checker,]);
        $dispatcher = Mockery::mock(EventDispatcher::class);

        // run command
        $cmd = new TestCommand($driveDiscoveryCmd, $checkerFactory, $this->cache, $dispatcher);
        Assert::same(-2, $cmd->run($input, $output));
    }

    /**
     * Run command without arguments test.
     *
     * @return void
     */
    public function testRunWithoutArguments(): void
    {
        $driveDiscoveryCmd = Mockery::mock(DriveDiscoveryCommand::class, ['detectSystemDrives' => ['/dev/sdx']]);
        $checker = Mockery::mock(Checker::class);
        $checkerFactory = Mockery::mock(CheckerFactory::class);
        $dispatcher = Mockery::mock(EventDispatcher::class);

        $cmd = new TestCommand($driveDiscoveryCmd, $checkerFactory, $this->cache, $dispatcher);
        Assert::same(-1, $cmd->run(new StringInput(''), new ConsoleOutput()));
    }
}

$test = new DriveTesterCommandTest($container);
$test->run();
