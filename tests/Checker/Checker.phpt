<?php

declare(strict_types=1);

namespace Tests\DriveTester;

use App\Checker\Checker;
use App\Checker\SharedStatusCache;
use App\Checker\Status;
use App\Drive\Drive;
use App\Drive\DriveFactory;
use Faker\Factory;
use Tester\Assert;
use Nette\DI\Container;
use Mockery;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

$container = require __DIR__ . '/../bootstrap.php';

/**
 * Checker test.
 */
class CheckerTest extends \Tester\TestCase
{
    /** @var Faker Faker */
    private $faker;

    /** @var SharedStatusCache Shared status cache */
    private $cache;

    /**
     * Class constructor.
     *
     * @param Containter $containter Nette DI container
     */
    public function __construct(Container $container)
    {
        $this->cache = $container->getByType('App\Checker\SharedStatusCache');
        $this->faker = Factory::create();
    }

    /**
     * Tear down test.
     *
     * @return void
     */
    public function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * No badblocks detected test.
     *
     * @return void
     */
    public function testNoBadblocks(): void
    {
        // prepare test
        $path = $this->faker->regexify('/dev/sd[a-z]{1,3}');
        $sn = $this->faker->regexify('[A-Z0-9]{10,15}');

        // run checker
        $status = $this->runChecker($path, $sn);

        // check status
        Assert::same($sn, $status->getSerialNumber());
        Assert::same(Status::STATE_DONE, $status->getState());
        Assert::same('OK', $status->getMessage());
    }

    /**
     * Bad blocks detected test.
     *
     * @return void
     */
    public function testBadblocks(): void
    {
        // prepare test
        $path = $this->faker->regexify('/dev/sd[a-z]{1,3}');
        $sn = $this->faker->regexify('[A-Z0-9]{10,15}');
        $badblocks = rand(1, 128);

        // run checker
        $status = $this->runChecker($path, $sn, $badblocks);

        // check status
        Assert::same($sn, $status->getSerialNumber());
        Assert::same(Status::STATE_ERROR, $status->getState());
        Assert::same(sprintf('%d bad blocks found', $badblocks), $status->getMessage());
    }

    /**
     * Run checker.
     *
     * @param string $path Drive path
     * @param string $sn Drive serial number
     * @param integer $badblocks Number of detected badblocks
     * @return Status
     */
    protected function runChecker(string $path, string $sn, int $badblocks = 0): Status
    {
        $drive = Mockery::mock(Drive::class, [
            'getSerialNumber' => $sn,
            'getPartedInfo' => 'partedInfo',
            'getSmartctlInfo' => 'smartctlInfo',
            'badblocks' => $badblocks,
            'isSsd' => false,
        ]);
        $driveFactory = Mockery::mock(DriveFactory::class, [
            'create' => $drive,
        ]);
        $dispatcher = Mockery::mock(
            EventDispatcherInterface::class,
            [
                'dispatch' => '',
            ]
        );

        $checker = new Checker($path, $driveFactory, $this->cache, $dispatcher);
        $checker->run();

        return $this->cache->getStatus($path);
    }
}

$test = new CheckerTest($container);
$test->run();
