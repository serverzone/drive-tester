<?php declare(strict_types=1);

namespace Tests\DriveTester\Command;

use App\Command\PartedCommand;
use App\Process\MockProcessFactory;
use Mockery;
use Nette\DI\Container;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * Parted command test.
 */
class PartedCommandTest extends \Tester\TestCase
{
    /** @var MockProcessFactory Process factory */
    private $processFactory;

    /** @var LoggerInterface Logger interface */
    private $logger;

    /**
     * Class constructor.
     *
     * @param Containter $containter Nette DI container
     */
    public function __construct(Container $container)
    {
        $this->processFactory = $container->getService('ProcessFactory');
        $this->logger = $container->getByType('Psr\Log\LoggerInterface');
    }

    /**
     * Tear down test.
     */
    protected function tearDown()
    {
        Mockery::close();
    }

    /**
     * Get partition information test.
     *
     * @return void
     */
    public function testPrint(): void
    {
        $output = "BYT;" . PHP_EOL . "/dev/sda:1000GB:scsi:512:512:gpt:ATA ST31000340NS:;";
        $this->processFactory->addCommand(['/bin/echo', $output]);
        Assert::same($output, $this->createCommad()->print('/dev/sda'));
    }

    /**
     * Command exit code test.
     *
     * @return void
     * @throws \App\Process\ProcessFailedException
     */
    public function testCommandExitCode(): void
    {
        $this->processFactory->addCommand(['/bin/bash', '-c', 'exit 1']);
        $this->createCommad()->print('/dev/sdb');
    }

    /**
     * Command not found test.
     *
     * @return void
     * @throws \App\Process\ProcessFailedException
     */
    public function testCommandNotFound(): void
    {
        $this->processFactory->addCommand(['failed_command']);
        $this->createCommad()->print('/dev/sdb');
    }

    /**
     * Create parted command.
     *
     * @return PartedCommand
     */
    protected function createCommad(): PartedCommand
    {
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch')->once();

        return new PartedCommand($this->processFactory, $this->logger, $eventDispatcher);
    }
}

$test = new PartedCommandTest($container);
$test->run();
