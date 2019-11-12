<?php

declare(strict_types=1);

namespace App\Event;

use App\Checker\Status;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Checker event.
 */
class CheckerEvent extends Event
{
    /** @var string Drive path */
    private $path;

    /** @var Status Checker status */
    private $status;

    /**
     * Class constructor.
     *
     * @param string $path Drive path (e.g. /dev/sda)
     * @param Status $status Checker status
     */
    public function __construct(string $path, Status $status)
    {
        $this->path = $path;
        $this->status = $status;
    }

    /**
     * Return drive path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Return checker status.
     *
     * @return Status
     */
    public function getStatus(): Status
    {
        return $this->status;
    }
}
