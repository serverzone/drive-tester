<?php

declare(strict_types=1);

namespace Tests\DriveTester;

use App\Command\SmartCtlCommand;
use App\Process\MockProcessFactory;
use Faker;
use Mockery;
use Nette\DI\Container;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * Get SMART ctl command test.
 */
class SmartCtlCommandTest extends \Tester\TestCase
{
    /** @var MockProcessFactory Process factory */
    private $processFactory;

    /** @var LoggerInterface Logger interface */
    private $logger;

    /** @var Faker Faker */
    private $faker;

    /**
     * Class constructor.
     *
     * @param Containter $containter Nette DI container
     */
    public function __construct(Container $container)
    {
        $this->processFactory = $container->getService('ProcessFactory');
        $this->logger = $container->getByType('Psr\Log\LoggerInterface');
        $this->faker = Faker\Factory::create();
    }

    /**
     * Tear down test.
     */
    protected function tearDown()
    {
        Mockery::close();
    }

    /**
     * Smart ctl get info test.
     *
     * @return void
     */
    public function testGetInfo(): void
    {
        $output = $this->faker->text;
        $this->processFactory->addCommand(['/bin/echo', $output]);
        Assert::same($output, $this->createCommad()->getInfo('/dev/sdb'));
    }

    /**
     * Command not found test.
     *
     * @return void
     * @throws \App\Process\ProcessFailedException
     */
    public function testCommandNotFound(): void
    {
        $this->processFactory->addCommand(['dddd']);
        $this->createCommad()->getInfo('/dev/sdb');
    }

    /**
     * Command exist codes test.
     *
     * @return void
     */
    public function testCommandExitCodes(): void
    {
        for ($exitCode = 1; $exitCode < 256; $exitCode += 2) {
            $this->processFactory->addCommand(['/bin/bash', '-c', 'exit ' . $exitCode]);
            $smartCtlCmd = $this->createCommad();
            Assert::exception(function () use ($smartCtlCmd) {
                $smartCtlCmd->getInfo('/dev/sdb');
            }, \App\Process\ProcessFailedException::class);
        }

        for ($exitCode = 0; $exitCode < 256; $exitCode += 2) {
            $this->processFactory->addCommand(['/bin/bash', '-c', 'exit ' . $exitCode]);
            $this->createCommad()->getInfo('/dev/sdb');
        }
    }

    /**
     * Create smartCtl command.
     *
     * @return SmartCtlCommand
     */
    protected function createCommad(): SmartCtlCommand
    {
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch')->once();

        return new SmartCtlCommand($this->processFactory, $this->logger, $eventDispatcher);
    }
}

$test = new SmartCtlCommandTest($container);
$test->run();
