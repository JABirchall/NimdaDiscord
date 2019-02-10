<?php

namespace Nimda\Core;

use CharlotteDunois\Yasmin\Client;
use Illuminate\Support\Collection;

class TimerContainer
{

    const CORE_TIMER = 'Nimda\\Core\\Timers\\';
    const CORE_TIMER_CONFIG = 'Nimda\\Configuration\\Core\\';

    protected $timers;

    public function __construct()
    {
        $this->timers = new Collection();
    }

    public function loadTimers(Client $client, $timers)
    {
        foreach ($timers as $timer) {
            $timerName = substr($timer, strlen(self::CORE_TIMER));
            printf("%- 50s %s", "Loading core timer [{$timerName}]", ":: ");

            if (!class_exists($timer)) {
                printf("Loading failed because class %s doesn't exist.\n", $timerName);
                continue;
            }

            if (!class_exists(self::CORE_TIMER_CONFIG.$timerName)) {
                printf("Loading failed because class %s does not have a config\n", $timerName);
                continue;
            }

            $config = $this->loadConfig($timerName);

            if($config === null) {
                printf("Timer %s with config file has not been loaded because it doesn't exist.\n", $timerName);
                continue;
            }

            $loadedTimer = new $timer($config);
            $this->timers->push($loadedTimer);

            $this->setTimer($config, $loadedTimer, $client);

            printf("Complete.\n");
        }
    }

    private function loadConfig($timer)
    {
        $class = \Nimda\Configuration\Core::class . '\\' . $timer;
        return $class::$config;
    }

    private function setTimer($config, $timer, $client)
    {
        if ($config['once'] === true) {
            $client->addTimer($config['interval'], [$timer, 'trigger']);
        } else {
            $client->addPeriodicTimer($config['interval'], [$timer, 'trigger']);
        }
    }
}