<?php

declare(strict_types=1);

namespace Tests\DriveTester\Command;

use Tester\Assert;
use Nette\DI\Container;
use App\Command\GetSerialNumberCommand;
use App\Process\MockProcessFactory;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * Get serial number Command test.
 */
class GetSerialNumberCommandTest extends \Tester\TestCase
{
    /** @var GetSerialNumberCommand Drive detector command */
    private $getSerialNumberCmd;

    /** @var MockProcessFactory Process factory */
    private $processFactory;

    /**
     * Class constructor.
     *
     * @param Containter $containter Nette DI container
     */
    public function __construct(Container $container)
    {
        $this->getSerialNumberCmd = $container->getByType('App\Command\GetSerialNumberCommand');
        $this->processFactory = $container->getService('ProcessFactory');
    }

    /**
     * Detect drive serial number test.
     *
     * @dataProvider GetSerialNumberCommand.ini
     *
     * @param string $output Command output
     * @param string|null $serialNumber Serial number
     * @return void
     */
    public function testDetectSerialNumber(string $output, ?string $serialNumber = null): void
    {
        var_dump($output);
        var_dump($serialNumber);
        $this->processFactory->addCommand(['/bin/echo', $output]);
        Assert::same($serialNumber, $this->getSerialNumberCmd->getSerialNumber('/dev/sdb'));
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
        $this->getSerialNumberCmd->getSerialNumber('/dev/sdb');
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
        $this->getSerialNumberCmd->getSerialNumber('/dev/sdb');
    }
}

$test = new GetSerialNumberCommandTest($container);
$test->run();
