<?php

declare(strict_types=1);

namespace Tests\DriveTester;

use Faker;
use Tester\Assert;
use Nette\DI\Container;
use App\Command\SmartCtlCommand;
use App\Process\MockProcessFactory;
use Tester\Expect;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * Get SMART ctl command test.
 */
class SmartCtlCommandTest extends \Tester\TestCase
{
    /** @var SmartCtlCommand SMART ctl command */
    private $smartCtlCmd;

    /** @var MockProcessFactory Process factory */
    private $processFactory;

    /** @var Faker Faker */
    private $faker;

    /**
     * Class constructor.
     *
     * @param Containter $containter Nette DI container
     */
    public function __construct(Container $container)
    {
        $this->smartCtlCmd = $container->getByType('App\Command\SmartCtlCommand');
        $this->processFactory = $container->getService('ProcessFactory');
        $this->faker = Faker\Factory::create();
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
        Assert::same($output, $this->smartCtlCmd->getInfo('/dev/sdb'));
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
        $this->smartCtlCmd->getInfo('/dev/sdb');
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
            $smartCtlCmd = $this->smartCtlCmd;
            Assert::exception(function () use ($smartCtlCmd) {
                $smartCtlCmd->getInfo('/dev/sdb');
            }, \App\Process\ProcessFailedException::class);
        }

        for ($exitCode = 0; $exitCode < 256; $exitCode += 2) {
            $this->processFactory->addCommand(['/bin/bash', '-c', 'exit ' . $exitCode]);
            $this->smartCtlCmd->getInfo('/dev/sdb');
        }
    }
}

$test = new SmartCtlCommandTest($container);
$test->run();
