<?php declare(strict_types=1);

namespace Tests\DriveTester;

use App\Command\BadblocksCommand;
use App\Command\DetectBadBlocksCommand;
use App\Process\MockProcessFactory;
use Faker;
use Mockery;
use Nette\DI\Container;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * Bad blocks command test.
 */
class DetectBadBlocksCommandTest extends \Tester\TestCase
{
    /** @var MockProcessFactory Process factory */
    private $processFactory;

    /** @var LoggerInterface Logger interface */
    private $logger;

    /**
     * Class constructor.
     *
     * @param Container $container
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
     * Detect bad blocks test.
     *
     * @return void
     */
    public function testDetectBadBlocks(): void
    {
        $faker = Faker\Factory::create();
        $output = $faker->text;
        $this->processFactory->addCommand(['/bin/echo', $output]);
        Assert::same(-1, $cmd = $this->createCommad()->detect('/dev/sdb', true));
        Assert::same(['/sbin/badblocks', '-v', '-e150', '-b8192', '-c8192', '-w', '/dev/sdb'], $this->processFactory->getOriginalCommand());

        $badBlocks = rand(10, 1024);
        $output = sprintf('Pass completed, %d bad blocks found.', $badBlocks);
        $this->processFactory->addCommand(['/bin/echo', $output]);
        Assert::same($badBlocks, $this->createCommad()->detect('/dev/sdb', false));
        Assert::same(['/sbin/badblocks', '-v', '-e150', '-b8192', '-c8192', '/dev/sdb'], $this->processFactory->getOriginalCommand());
    }

    /**
     * Command exit code test.
     *
     * @return void
     * @throws App\Process\ProcessFailedException
     */
    public function testCommandExitCode(): void
    {
        $this->processFactory->addCommand([' / bin / bash', ' - c', 'exit 1']);
        $this->createCommad()->detect(' / dev / sdb');
    }

    /**
     * Command not found test.
     *
     * @return void
     * @throws App\Process\ProcessFailedException
     */
    public function testCommandNotFound(): void
    {
        $this->processFactory->addCommand(['dddd']);
        $this->createCommad()->detect(' / dev / sdb');
    }

    /**
     * Create bad blocks command.
     *
     * @return BadblocksCommand
     */
    protected function createCommad(): BadblocksCommand
    {
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch')->once();

        return new BadblocksCommand($this->processFactory, $this->logger, $eventDispatcher);
    }
}

$test = new DetectBadBlocksCommandTest($container);
$test->run();
