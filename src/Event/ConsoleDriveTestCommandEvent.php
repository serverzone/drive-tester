<?php

declare(strict_types=1);

namespace App\Event;

use App\Checker\Status;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Test command event.
 */
class ConsoleDriveTestCommandEvent extends Event
{
    /** @var Status[] Array of statuses */
    private $statuses;

    /**
     * Class constructor.
     *
     * @param Status[] $statuses Array of statuses
     */
    public function __construct(array $statuses)
    {
        $this->statuses = $statuses;
    }

    /**
     * Return array of statuses.
     *
     * @return Status[]
     */
    public function getStatuses(): array
    {
        return $this->statuses;
    }
}
