<?php

namespace Nimda\Core;

use CharlotteDunois\Yasmin\Client;
use Illuminate\Support\Collection;
use Nimda\Configuration\Discord;

final class TimerContainer
{

    const CORE_TIMER = 'Nimda\\Core\\Timers\\';
    const CORE_TIMER_CONFIG = 'Nimda\\Configuration\\Core\\';
    const PUBLIC_TIMER = 'Nimda\\Timers\\';
    const PUBLIC_TIMER_CONFIG = 'Nimda\\Timers\\Configuration\\';
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $timers;

    protected $client;

    /**
     * TimerContainer constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->timers = new Collection();
        $this->client = $client;
    }

    /**
     * Setup timers

     */
    public function loadTimers()
    {
            $this->loadCoreTimers(Discord::$config['timers']['core']);
            $this->loadPublicTimers(Discord::$config['timers']['public']);

            printf("Loading timers completed.\n");
    }

    /**
     * Setup core timers
     * @param array $timers
     */
    public function loadCoreTimers(array $timers)
    {
        foreach ($timers as $timer) {
            if(!$this->precheckTimers(self::CORE_TIMER, $timer)) {
                continue;
            }

            $config = $this->loadConfig(self::CORE_TIMER, $timer);

            if($config === null) {
                continue;
            }

            $loadedTimer = new $timer($config);
            $this->timers->push($loadedTimer);

             $this->setTimer($loadedTimer, $config);            printf("Completed\n");
        }
    }

    /**
     * Setup public timers
     * @param array $timers
     */
    public function loadPublicTimers(array $timers)
    {
        foreach ($timers as $timer) {
            if(!$this->precheckTimers(self::PUBLIC_TIMER, $timer)) {
                continue;
            }

            $config = $this->loadConfig(self::PUBLIC_TIMER, $timer);

            if($config === null) {
                continue;
            }

            $loadedTimer = new $timer($config);
            $this->timers->push($loadedTimer);

            $this->setTimer($loadedTimer, $config);
            printf("Completed\n");
        }
    }

    /**
     * Validate a timer is correctly setup before loading
     * @param $namespace
     * @param $timer
     * @return bool
     */
    private function precheckTimers($namespace, $timer)
    {
        $timerName = substr($timer, strlen($namespace));

        $type = ($namespace == self::CORE_TIMER) ? 'core' : 'public';

        printf("%- 50s %s", "Loading {$type} timer [{$timerName}]", ":: ");

        if (!class_exists($timer)) {
            printf("Loading failed because class %s doesn't exist.\n", $timerName);
            return false;
        }

        if(!is_subclass_of($timer, Timer::class))
        {
            printf("Loading failed because class %s doesn't extend %s.\n", $timerName, Timer::class);
            return false;
        }

        return true;
    }

    /**
     * Load a plugin for a timer
     * @param $namespace
     * @param $timer
     * @return array|null
     */
    private function loadConfig($namespace, $timer)
    {
        $timerName = substr($timer, strlen($namespace));
        $class = ($namespace == self::CORE_TIMER) ?
            self::CORE_TIMER_CONFIG . $timerName :
            self::PUBLIC_TIMER_CONFIG . $timerName;

        if (!class_exists($class)) {
            printf("Loading failed because class %s does not have a config\n", $timerName);
            return null;
        }

        return $class::$config;
    }

    /**
     * Add the timer to the timer loop set by timout
     * @param Timer $timer
     * @param array $config
     */
    private function setTimer(Timer $timer, $config)
    {
        if ($config['once'] === true) {
            $this->client->addTimer($config['interval'], [$timer, 'trigger']);
            return;
        }

        $this->client->addPeriodicTimer($config['interval'], [$timer, 'trigger']);
    }
}