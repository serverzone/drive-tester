<?php

declare(strict_types=1);

namespace App\Event\Subscriber\SendNotification;

use App\Event\ConsoleDriveTestCommandEvent;
use GuzzleHttp;
use LogicException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Mattermost web hook.
 */
class MattermostWebHook implements EventSubscriberInterface
{
    /** @var string Mattermost web hook url */
    protected $url;

    /**
     * Return array of events.
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleDriveTestCommandEvent::class => 'processEvent',
        ];
    }

    /**
     * Class constructor.
     *
     * @param string $url Mattermost web hook url
     */
    public function __construct(string $url)
    {
        if (strlen($url) === 0) {
            throw new LogicException('Mattemost web hook url is empty!');
        }
        $this->url = $url;
    }

    /**
     * Process event.
     *
     * @param ConsoleDriveTestCommandEvent $event Event
     * @return void
     */
    public function processEvent(ConsoleDriveTestCommandEvent $event): void
    {
        $message = 'Drive tester result:' . PHP_EOL;
        foreach ($event->getStatuses() as $path => $status) {
            $message .= sprintf('   %s (%s): %s%s', $path, $status->getSerialNumber(), $status->toString(), PHP_EOL);
        }

        $client = new GuzzleHttp\Client();
        $client->request('POST', $this->url, [
            'form_params' => [
                'payload' => json_encode(['text' => $message])
            ]
        ]);
    }
}
