<?php declare(strict_types=1);

namespace Tests\DriveTester\Command;

use App\Command\BaseCommand;
use App\Process\IProcessFactory;
use App\Process\MockProcessFactory;
use App\Process\Process;
use App\Process\ProcessFactory;
use App\Process\ProcessFailedException;
use Mockery;
use Nette\DI\Container;
use Symfony\Component\Process\Exception\ProcessSignaledException;
use Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * Abstract class BaseCommand test.
 *
 * @testCase
 */
class BaseCommandTest extends \Tester\TestCase
{
    /** @var BaseCommand */
    private $baseCommand;

    /** @var Container */
    private $container;

    /**
     * Class constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    protected function setUp(): void
    {
        $processFactory = new ProcessFactory();
        $logger = $this->container->getByType('Psr\Log\LoggerInterface');
        $this->baseCommand = new class($processFactory, $logger) extends BaseCommand {
            public function echo(string $msg): ?string
            {
                return $this->runCommand(['/bin/echo', $msg]);
            }

            public function getSuccessful(): bool
            {
                return $this->isSuccessful();
            }
        };
    }

    /**
     * Run process test.
     */
    public function testRunProcess(): void
    {
        $word = 'word';

        Assert::same($word, $this->baseCommand->echo($word));
        Assert::true($this->baseCommand->getProcess()->isSuccessful());
    }

    /**
     * Get process exception test.
     *
     * @throws \RuntimeException
     */
    public function testGetProcessException(): void
    {
        $this->baseCommand->getProcess();
    }

    /**
     * Is successful exception test.
     *
     * @throws \RuntimeException
     */
    public function testIsSucessfullException(): void
    {
        $this->baseCommand->getSuccessful();
    }
}

(new BaseCommandTest($container))->run();
