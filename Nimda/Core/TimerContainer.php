<?php

namespace Nimda\Core;

use CharlotteDunois\Yasmin\Client;
use Illuminate\Support\Collection;

class TimerContainer
{

    const CORE_TIMER = 'Nimda\\Core\\Timers\\';
    const CORE_TIMER_CONFIG = 'Nimda\\Configuration\\Core\\';
    const PUBLIC_TIMER = 'Nimda\\Timers\\';
    const PUBLIC_TIMER_CONFIG = 'Nimda\\Timers\\Configuration\\';

    protected $timers;

    public function __construct()
    {
        $this->timers = new Collection();
    }

    public function loadTimers(Client $client, $timers)
    {
            $this->loadCoreTimers($timers['core'], $client);
            $this->loadPublicTimers($timers['public'], $client);

            printf("Loading timers completed.\n");
    }

    public function loadCoreTimers($timers, Client $client)
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

    public function loadPublicTimers($timers, Client $client)
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

            $this->setTimer($config, $loadedTimer, $client);
            printf("Completed\n");
        }
    }

    private function precheckTimers($namespace, $timer)
    {
        $timerName = substr($timer, strlen($namespace));

        $type = ($namespace == self::CORE_TIMER) ? 'core' : 'public';

        printf("%- 50s %s", "Loading {$type} timer [{$timerName}]", ":: ");

        if (!class_exists($timer)) {
            printf("Loading failed because class %s doesn't exist.\n", $timerName);
            return false;
        }

        return true;
    }

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

    private function setTimer($config, $timer, $client)
    {
        if ($config['once'] === true) {
            $client->addTimer($config['interval'], [$timer, 'trigger']);
            return;
        }

        $client->addPeriodicTimer($config['interval'], [$timer, 'trigger']);
    }
}