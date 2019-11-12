<?php

declare(strict_types=1);

namespace App\Checker;

use App\Drive\DriveFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Checker factory.
 */
class CheckerFactory
{
    /** @var DriveFactory Drive factory */
    private $driveFactory;

    /** @var SharedStatusCache Shared status cache */
    private $cache;

    /** @var EventDispatcherInterface Event dispatcher */
    private $dispatcher;

    /**
     * Class constructor.
     *
     * @param DriveFactory $driveFactory Drive factory
     * @param SharedStatusCache $cache Shared status cache
     * @param EventDispatcherInterface $dispatcher Event dispatcher
     */
    public function __construct(DriveFactory $driveFactory, SharedStatusCache $cache, EventDispatcherInterface $dispatcher)
    {
        $this->driveFactory = $driveFactory;
        $this->cache = $cache;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Create DriveChecker instance.
     *
     * @param string $path Drive path
     * @return Checker
     */
    public function create(string $path): Checker
    {
        return new Checker($path, $this->driveFactory, $this->cache, $this->dispatcher);
    }
}
