<?php

declare(strict_types=1);

namespace App\Event\Subscriber\StoreProcessOutput;

use Contributte\Utils\FileSystem;
use App\Event\CommandEvent;
use App\Command\IDispatcherCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Store process output to file event subscriber.
 */
final class FileStorage implements EventSubscriberInterface
{
    /** @var string Store dir prefix */
    private $storeDirPrefix;

    /**
     * Class constructor.
     *
     * @param string $storeDirPrefix Storage directory path prefix
     */
    public function __construct(string $storeDirPrefix)
    {
        $this->storeDirPrefix = $storeDirPrefix;
    }

    /**
     * Return array of events.
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CommandEvent::class => 'onCommandEvent',
        ];
    }

    /**
     * Store process outputs to file.
     *
     * @param CommandEvent $event Command event
     * @return void
     */
    public function onCommandEvent(CommandEvent $event): void
    {
        $filename = $event->getOption('label');
        if ($filename !== null) {
            $process = $event->getCommand()->getProcess();
            $storeDir = sprintf('%s/%s/%s/', $this->storeDirPrefix, $event->getOption('serialNumber'), $event->getOption('startedAt')->format('Ymd-His'));
            FileSystem::createDir($storeDir);

            file_put_contents($storeDir . $filename . '.stdout', $process->getOutput());
            file_put_contents($storeDir . $filename . '.stderr', $process->getErrorOutput());
            file_put_contents($storeDir . $filename . '.exitcode', $process->getExitCode());
            file_put_contents($storeDir . $filename . '.time', $process->getRunningTime());
        }
    }
}
