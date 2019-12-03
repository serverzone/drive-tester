<?php

declare(strict_types=1);

namespace App\Event;

use App\Command\IDispatcherCommand;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Command event.
 */
class CommandEvent extends Event
{
    /** @var IDispatcherCommand Command */
    private $command;

    /** @var Array Event options */
    private $options;

    /**
     * Class constructor.
     *
     * @param IDispatcherCommand $command Command
     * @param array $options Event options
     */
    public function __construct(IDispatcherCommand $command, array $options = [])
    {
        $this->command = $command;
        $this->options = $options;
    }

    /**
     * Return command.
     *
     * @return IDispatcherCommand
     */
    public function getCommand(): IDispatcherCommand
    {
        return $this->command;
    }

    /**
     * Return event options.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Return event option.
     *
     * @param string $key Option key
     * @param mixed $defaultValue Default value
     * @return mixed
     */
    public function getOption(string $key, $defaultValue = null)
    {
        return $this->options[$key] ?? $defaultValue;
    }
}
