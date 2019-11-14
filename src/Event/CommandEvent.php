<?php

declare(strict_types=1);

namespace App\Event;

use App\Command\ICommand;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Command event.
 */
class CommandEvent extends Event
{
    /** @var ICommand Command */
    private $command;

    /** @var Array Event options */
    private $options;

    /**
     * Class constructor.
     *
     * @param ICommand $command Command
     * @param array $options Event options
     */
    public function __construct(ICommand $command, array $options = [])
    {
        $this->command = $command;
        $this->options = $options;
    }

    /**
     * Return command.
     *
     * @return ICommand
     */
    public function getCommand(): ICommand
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
