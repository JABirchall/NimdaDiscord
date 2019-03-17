<?php

namespace Nimda\Core;
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
    const PUBLIC_EVENT_CONFIG = 'Nimda\\Plugins\\Configuration\\';

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $events;

    /**
     * EventContainer constructor.
     */
    public function __construct()
    {
        $this->events = new Collection();
    }

    /**
     * Loading events
     */
    public function loadEvents()
    {
        $this->loadCoreEvents(Discord::$config['events']['core']);
        $this->loadPublicEvents(Discord::$config['events']['public']);

        printf("Loading Events completed\n");
    }

    /**
     * Setup Core Events
     * @param array $events
     */
    private function loadCoreEvents(array $events)
    {
        foreach ($events as $event) {
            if(!$this->precheckEvent(self::CORE_EVENT, $event)) {
                continue;
            }

            $config = $this->loadConfig(self::CORE_EVENT, $event);

            if($config === null) {
                continue;
            }

            $loadedEvent = new $event($config);
            $this->setUserTrigger($loadedEvent, $config);

            printf("Completed\n");
        }
    }

    /**
     * Setup Public Events
     * @param array $events
     */
    private function loadPublicEvents(array $events)
    {
        foreach ($events as $event) {
            if(!$this->precheckEvent(self::PUBLIC_EVENT, $event)) {
                continue;
            }

            $config = $this->loadConfig(self::PUBLIC_EVENT, $event);

            if($config === null) {
                continue;
            }

            $loadedEvent = new $event($config);
            $this->setUserTrigger($loadedEvent, $config);

            printf("Completed\n");
        }
    }

    /**
     * Validate a event is valid before loading it.
     * @param $namespace
     * @param $event
     * @return bool
     */
    private function precheckEvent($namespace, $event)
    {
        $eventName = substr($event, strlen($namespace));

        $type = ($namespace == self::CORE_EVENT) ? 'core' : 'public';

        printf("%- 50s %s", "Loading {$type} event [{$eventName}]", ":: ");

        if (!class_exists($event)) {
            printf("Loading failed because class %s doesn't exist.\n", $eventName);
            return false;
        }

        if(!is_subclass_of($event, UserEvent::class))
        {
            printf("Loading failed because class %s doesn't extend %s.\n", $eventName, UserEvent::class);
            return false;
        }

        return true;
    }

    /**
     * @param array $config
     * @param UserEvent $event
     */
    private function setUserTrigger(UserEvent $event, array $config)
    {
        if(array_key_exists('trigger', $config)) {
            $trigger = $config['trigger'];
            $this->events->push([$trigger => $event]);
        }
    }

    /**
     * Load the event configuration
     * @param $namespace
     * @param $event
     * @return array|null
     */
    private function loadConfig($namespace, $event)
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

    /**
     * Waiting for clients to join the server
     * @param GuildMember $member
     */
    public function guildMemberAdd(GuildMember $member)
    {
        $events = $this->events->filter(function ($event) {
           return 'guildMemberAdd' === array_keys($event)[0];
        })->collapse();

        if($events->isEmpty()){
            return;
        }

        $events->each(function (UserEvent $event) use ($member) {
            $event->userEventTrigger($member);
        });
    }
}
