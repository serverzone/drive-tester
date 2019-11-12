<?php

declare(strict_types=1);

namespace App\Checker;

use Nette\Utils\Json;

/**
 * Drive checker status.
 */
class Status
{
    const STATE_RUNNING = 0;
    const STATE_DONE = 1;
    const STATE_ERROR = -1;

    /** @var array Tags for states */
    private $tags = [
        self::STATE_DONE => 'info',
        self::STATE_ERROR => 'error',
    ];

    /** @var int State */
    private $state;

    /** @var string Message */
    private $message;

    /** @var string Serial number */
    private $serialNo;

    /**
     * Create status from json as string.
     *
     * @param string $json Json as string
     * @return Status
     */
    public static function fromJsonString(string $json): Status
    {
        $decode = Json::decode($json);
        $status = new Status($decode->state, $decode->message, $decode->serialNumber);

        return $status;
    }

    /**
     * Class constructor.
     *
     * @param integer $state Initial state value
     * @param string $message Initial state message
     * @param string $serialNo Serial number
     */
    public function __construct(int $state = self::STATE_RUNNING, string $message = '', string $serialNo = '')
    {
        $this->state = $state;
        $this->message = $message;
        $this->serialNo = $serialNo;
    }

    /**
     * Return state.
     *
     * @return integer
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * Update status.
     *
     * @param integer $state New state
     * @param string|null $message Message
     * @return void
     */
    public function updateState(int $state, ?string $message = null): void
    {
        if ($this->state !== self::STATE_ERROR || $state === self::STATE_ERROR) {
            $this->state = $state;
            if ($message !== null) {
                $this->message = $message;
            }
        }
    }

    /**
     * Set disk serial number.
     *
     * @param string|null $serialNo Serial number
     * @return void
     */
    public function setSerialNumber(?string $serialNo): void
    {
        if ($serialNo !== null) {
            $this->serialNo = $serialNo;
        }
    }

    /**
     * Return disk serial number.
     *
     * @return string
     */
    public function getSerialNumber(): string
    {
        return $this->serialNo;
    }

    /**
     * Return status message.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Return status as string.
     *
     * @return string
     */
    public function toString(): string
    {
        if (isset($this->tags[$this->state])) {
            return sprintf('<%s>%s</%s>', $this->tags[$this->state], $this->message, $this->tags[$this->state]);
        }

        return $this->message;
    }

    /**
     * Return status as json as string.
     *
     * @return string
     */
    public function toJsonString(): string
    {
        return Json::encode([
            'state' => $this->state,
            'message' => $this->message,
            'serialNumber' => $this->serialNo,
        ]);
    }
}
