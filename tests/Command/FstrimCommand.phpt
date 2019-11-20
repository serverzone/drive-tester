<?php

declare(strict_types=1);

namespace Tests\DriveTester\Command;

use App\Command\FstrimCommand;
use Tester\Assert;
use Nette\DI\Container;
use App\Process\MockProcessFactory;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * Fstrim command test.
 */
class FstrimCommandTest extends \Tester\TestCase
{
     /** @var FstrimCommand Fstrim command */
     private $fstrimCmd;

     /** @var MockProcessFactory Process factory */
     private $processFactory;

     /**
      * Class constructor.
      *
      * @param Containter $containter Nette DI container
      */
    public function __construct(Container $container)
    {
         $this->fstrimCmd = $container->getByType('App\Command\FstrimCommand');
         $this->processFactory = $container->getService('ProcessFactory');
    }

     /**
      * Fstrim execute test.
      *
      * @return void
      */
    public function testExecute(): void
    {
         $this->processFactory->addCommand(['/bin/echo', '']);
         $this->fstrimCmd->execute('/dev/sda1');
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
         $this->fstrimCmd->execute('/dev/sda1');
    }

     /**
      * Command not found test.
      *
      * @throws \App\Process\ProcessFailedException
      * @return void
      */
    public function testCommandNotFound(): void
    {
         $this->processFactory->addCommand(['failed_command']);
         $this->fstrimCmd->execute('/dev/sda1');
    }
}

$test = new FstrimCommandTest($container);
$test->run();
