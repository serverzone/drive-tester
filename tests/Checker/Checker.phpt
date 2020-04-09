<?php declare(strict_types=1);

namespace Tests\DriveTester;

use App\Checker\Checker;
use App\Checker\SharedStatusCache;
use App\Checker\Status;
use App\Drive\Drive;
use App\Drive\DriveFactory;
use Faker\Factory;
use Mockery;
use Nette\DI\Container;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tester\Assert;

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
     * @param Container $container
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
     * @dataProvider dataProvider
     *
     * @param bool $isSdd
     * @param bool $ssdWriteTestEnabled
     * @return void
     */
    public function testNoBadblocks(bool $isSdd, bool $ssdWriteTestEnabled): void
    {
        // prepare test
        $path = $this->faker->regexify('/dev/sd[a-z]{1,3}');
        $sn = $this->faker->regexify('[A-Z0-9]{10,15}');

        // run checker
        $status = $this->runChecker($path, $sn, $isSdd, $ssdWriteTestEnabled, 0);

        // check status
        Assert::same($sn, $status->getSerialNumber());
        Assert::same(Status::STATE_DONE, $status->getState());
        Assert::same('OK', $status->getMessage());
    }

    /**
     * Bad blocks detected test.
     *
     * @dataProvider dataProvider
     *
     * @param bool $isSdd
     * @param bool $ssdWriteTestEnabled
     * @return void
     */
    public function testBadblocks(bool $isSdd, bool $ssdWriteTestEnabled): void
    {
        // prepare test
        $path = $this->faker->regexify('/dev/sd[a-z]{1,3}');
        $sn = $this->faker->regexify('[A-Z0-9]{10,15}');
        $badblocks = rand(1, 128);

        // run checker
        $status = $this->runChecker($path, $sn, $isSdd, $ssdWriteTestEnabled, $badblocks);

        // check status
        Assert::same($sn, $status->getSerialNumber());
        Assert::same(Status::STATE_ERROR, $status->getState());
        Assert::same(sprintf('%d bad blocks found', $badblocks), $status->getMessage());
    }

    /**
     * Tests data provider.
     *
     * @return array
     */
    protected function dataProvider(): array
    {
        return [
            [true, true],
            [true, false],
            [false, true],
            [false, false],
        ];
    }

    /**
     * Run checker.
     *
     * @param string $path Drive path
     * @param string $sn Drive serial number
     * @param bool $ssdWriteTestEnabled Enable write test for ssd flag
     * @param integer $badblocks Number of detected badblocks
     * @return Status
     * @throws \Exception
     */
    protected function runChecker(string $path, string $sn, bool $isSsd, bool $ssdWriteTestEnabled = false, int $badblocks = 0): Status
    {
        $drive = Mockery::mock(Drive::class, [
            'getSerialNumber' => $sn,
            'getPartedInfo' => 'partedInfo',
            'getSmartctlInfo' => 'smartctlInfo',
            'isSsd' => $isSsd,
        ]);
        if ($isSsd) {
            $drive->shouldReceive('fstrim');
        }
        $drive->shouldReceive('badblocks')
            ->with($isSsd ? $ssdWriteTestEnabled : true, \Mockery::type('array'))
            ->once()
            ->andReturn($badblocks);
        $driveFactory = Mockery::mock(DriveFactory::class, [
            'create' => $drive,
        ]);
        $dispatcher = Mockery::mock(
            EventDispatcherInterface::class,
            [
                'dispatch' => '',
            ]
        );

        $checker = new Checker($path, $ssdWriteTestEnabled, $driveFactory, $this->cache, $dispatcher);
        $checker->run();

        return $this->cache->getStatus($path);
    }
}

$test = new CheckerTest($container);
$test->run();
