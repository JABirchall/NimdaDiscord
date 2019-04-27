<?php declare(strict_types=1);

namespace Nimda\Core;

use CharlotteDunois\Yasmin\Client;
use CharlotteDunois\Yasmin\Models\GuildMember;
use Illuminate\Support\Collection;
use Nimda\Configuration\Discord;

/**
 * Class EventContainer
 * @package Nimda\Core
 */
final class EventContainer
{
    const CORE_EVENT = 'Nimda\\Core\\Events\\';
    const CORE_EVENT_CONFIG = 'Nimda\\Configuration\\Core\\';
    const PUBLIC_EVENT = 'Nimda\\Events\\';
    const PUBLIC_EVENT_CONFIG = 'Nimda\\Events\\Configuration\\';

    /**
     * @var \Illuminate\Support\Collection $events
     */
    protected $events;
    /**
     * @var Client
     */
    private $client;

    /**
     * EventContainer constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->events = new Collection();
        $this->client = $client;
    }

    /**
     * Loading events
     */
    public function loadEvents(): void
    {
        $this->loadCoreEvents(Discord::$config['events']['core']);
        $this->loadPublicEvents(Discord::$config['events']['public']);

        printf("Loading Events completed\n");
    }

    /**
     * @param array $events
     * @internal Setup Core Events
     *
     */
    private function loadCoreEvents(array $events): void
    {
        foreach ($events as $event) {
            if (!$this->precheckEvent(self::CORE_EVENT, $event)) {
                continue;
            }

            $config = $this->loadConfig(self::CORE_EVENT, $event);

            if ($config === null) {
                continue;
            }
            
            /** @var Event $loadedEvent */
            $loadedEvent = new $event($config);

            if(!$loadedEvent->isConfigured()) {
                printf("Loading failed because class %s is not configured correctly\n", $command);
                continue;
            }

            if (method_exists($loadedEvent, 'install')) {
                $loadedEvent->install();
            }

            $this->setTrigger($loadedEvent, $config);

            printf("Completed\n");
        }
    }

    /**
     * @param array $events
     * @internal Setup Public Events
     *
     */
    private function loadPublicEvents(array $events): void
    {
        foreach ($events as $event) {
            if (!$this->precheckEvent(self::PUBLIC_EVENT, $event)) {
                continue;
            }

            $config = $this->loadConfig(self::PUBLIC_EVENT, $event);

            if ($config === null) {
                continue;
            }

            /** @var Event $loadedEvent */
            $loadedEvent = new $event($config);

            if(!$loadedEvent->isConfigured()) {
                printf("Loading failed because class %s is not configured correctly\n", $command);
                continue;
            }

            if (method_exists($loadedEvent, 'install')) {
                $loadedEvent->install();
            }

            $this->setTrigger($loadedEvent, $config);

            printf("Completed\n");
        }
    }

    /**
     * @param $namespace
     * @param $event
     *
     * @return bool
     * @internal Validate a event is valid before loading it.
     *
     */
    private function precheckEvent($namespace, $event): bool
    {
        $eventName = substr($event, strlen($namespace));

        $type = ($namespace == self::CORE_EVENT) ? 'core' : 'public';

        printf("%- 50s %s", "Loading {$type} event [{$eventName}]", ":: ");

        if (!class_exists($event)) {
            printf("Loading failed because class %s doesn't exist.\n", $eventName);
            return false;
        }

        if (!is_subclass_of($event, Event::class)) {
            printf("Loading failed because class %s doesn't extend %s.\n", $eventName, Event::class);
            return false;
        }

        return true;
    }

    /**
     * @param array $config
     * @param Event $event
     * @internal Set the event trigger mapped to the plugin
     *
     */
    private function setTrigger(Event $event, array $config): void
    {
        foreach ($config['trigger']['events'] as $triggerEvent) {
            $this->client->on($triggerEvent, [$event, $triggerEvent]);
        }
    }

    /**
     * @param $namespace
     * @param $event
     *
     * @return array|null
     * @internal Load the event configuration
     *
     */
    private function loadConfig($namespace, $event): ?array
    {
        $event = substr($event, strlen($namespace));
        $class = ($namespace == self::CORE_EVENT) ?
            self::CORE_EVENT_CONFIG . $event :
            self::PUBLIC_EVENT_CONFIG . $event;

        if (!class_exists($class)) {
            printf("Loading failed because event %s does not have a config\n", $event);
            return null;
        }

        return $class::$config;
    }

}
