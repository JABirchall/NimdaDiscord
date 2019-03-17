<?php

namespace Nimda\Core;

use CharlotteDunois\Yasmin\Client;
use Illuminate\Support\Collection;

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

    /**
     * TimerContainer constructor.
     */
    public function __construct()
    {
        $this->timers = new Collection();
    }

    /**
     * Setup timers
     * @param Client $client
     * @param array $timers
     */
    public function loadTimers(Client $client, array $timers)
    {
            $this->loadCoreTimers($timers['core'], $client);
            $this->loadPublicTimers($timers['public'], $client);

            printf("Loading timers completed.\n");
    }

    /**
     * Setup core timers
     * @param array $timers
     * @param Client $client
     */
    public function loadCoreTimers(array $timers, Client $client)
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

            $this->setTimer($config, $loadedTimer, $client);
            printf("Completed\n");
        }
    }

    /**
     * Setup public timers
     * @param array $timers
     * @param Client $client
     */
    public function loadPublicTimers(array $timers, Client $client)
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

            $this->setTimer($client, $loadedTimer, $config);
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
     * @param Client $client
     * @param Timer $timer
     * @param array $config
     */
    private function setTimer(Client $client, Timer $timer, $config)
    {
        if ($config['once'] === true) {
            $client->addTimer($config['interval'], [$timer, 'trigger']);
            return;
        }

        $client->addPeriodicTimer($config['interval'], [$timer, 'trigger']);
    }
}