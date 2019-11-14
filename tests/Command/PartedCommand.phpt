<?php

declare(strict_types=1);

namespace Tests\DriveTester\Command;

use Tester\Assert;
use Nette\DI\Container;
use App\Command\PartedCommand;
use App\Process\MockProcessFactory;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * Parted command test.
 */
class MdadmCommandTest extends \Tester\TestCase
{
     /** @var PartedCommand Parted command */
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
         $this->mdadmCmd = $container->getByType('App\Command\PartedCommand');
         $this->processFactory = $container->getService('ProcessFactory');
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
         Assert::same($output, $this->mdadmCmd->print('/dev/sda'));
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
         $this->mdadmCmd->print('/dev/sdb');
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
         $this->mdadmCmd->print('/dev/sdb');
    }
}

$test = new MdadmCommandTest($container);
$test->run();
