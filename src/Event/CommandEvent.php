<?php

declare(strict_types=1);

namespace App\Event;

use App\Process\Process;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Command event.
 */
class CommandEvent extends Event
{
    /** @var Process Process */
    private $process;

    /** @var Array Event options */
    private $options;

    /**
     * Class constructor.
     *
     * @param Process $process Process
     * @param array $options Event options
     */
    public function __construct(Process $process, array $options = [])
    {
        $this->process = $process;
        $this->options = $options;
    }

    /**
     * Return process.
     *
     * @return Process
     */
    public function getProcess(): Process
    {
        return $this->process;
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
