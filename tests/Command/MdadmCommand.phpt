<?php

declare(strict_types=1);

namespace Tests\DriveTester\Command;

use App\Command\MdadmCommand;
use Tester\Assert;
use Nette\DI\Container;
use App\Process\MockProcessFactory;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * Mdadm command test.
 */
class MdadmCommandTest extends \Tester\TestCase
{
     /** @var MdadmCommand Mdadm command */
     private $mdadmCmd;

     /** @var MockProcessFactory Process factory */
     private $processFactory;

     /**
      * Class constructor.
      *
      * @param Containter $containter Nette DI container
      */
    public function __construct(Container $container)
    {
         $this->mdadmCmd = $container->getByType('App\Command\MdadmCommand');
         $this->processFactory = $container->getService('ProcessFactory');
    }

     /**
      * Mdadm query detail test.
      *
      * @return void
      */
    public function testQueryDetail(): void
    {
         $output = "mdadm output";

         $this->processFactory->addCommand(['/bin/echo', $output]);
         Assert::same($output, $this->mdadmCmd->queryDetail('/dev/md0'));
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
         $this->mdadmCmd->queryDetail('/dev/md0');
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
         $this->mdadmCmd->queryDetail('/dev/md0');
    }
}

$test = new MdadmCommandTest($container);
$test->run();
