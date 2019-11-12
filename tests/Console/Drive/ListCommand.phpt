<?php

declare(strict_types=1);

namespace Tests\Console\Drive;

use Tester\Assert;
use Nette\DI\Container;
use Contributte\Console\Application;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Drive list console command test.
 */
class ListCommandTest extends \Tester\TestCase
{

    /** @var Application Console application */
    private $application;

    /**
     * Class constructor.
     *
     * @param Containter $containter Nette DI container
     */
    public function __construct(Container $container)
    {
        $this->application = $container->getByType('Contributte\Console\Application');
    }

    /**
     * Run command test.
     */
    public function testRun(): void
    {
        $this->application->setDefaultCommand('drive:list', true);
        $this->application->setAutoExit(false);
        Assert::same(0, $this->application->run());
    }
}

$test = new ListCommandTest($container);
$test->run();
