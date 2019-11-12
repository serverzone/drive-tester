<?php

declare(strict_types=1);

namespace Tests\DriveTester;

use Tester\Assert;
use Nette\DI\Container;
use App\Command\DetectBadBlocksCommand;
use App\Process\MockProcessFactory;
use Faker;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * Get serial number Command test.
 */
class DetectBadBlocksCommandTest extends \Tester\TestCase
{
    /** @var DetectBadBlocksCommand Detect bad blocks command */
    private $detectBadBlocksCmd;

    /** @var MockProcessFactory Process factory */
    private $processFactory;

    /**
     * Class constructor.
     *
     * @param Containter $containter Nette DI container
     */
    public function __construct(Container $container)
    {
        $this->detectBadBlocksCmd = $container->getByType('App\Command\BadblocksCommand');
        $this->processFactory = $container->getService('ProcessFactory');
    }

    /**
     * Detect bad blocks test.
     *
     * @param string $output Command output
     * @param int $count Bad blocks counter
     * @return void
     */
    public function testDetectBadBlocks(): void
    {
        $faker = Faker\Factory::create();
        $output = $faker->text;
        $this->processFactory->addCommand(['/bin/echo', $output]);
        Assert::same(-1, $this->detectBadBlocksCmd->detect('/dev/sdb'));

        $badBlocks = rand(10, 1024);
        $output = sprintf('Pass completed, %d bad blocks found.', $badBlocks);
        $this->processFactory->addCommand(['/bin/echo', $output]);
        Assert::same($badBlocks, $this->detectBadBlocksCmd->detect('/dev/sdb'));
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
        $this->detectBadBlocksCmd->detect('/dev/sdb');
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
        $this->detectBadBlocksCmd->detect('/dev/sdb');
    }
}

$test = new DetectBadBlocksCommandTest($container);
$test->run();
