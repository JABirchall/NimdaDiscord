<?php declare(strict_types=1);

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
     * @var \Illuminate\Support\Collection $timers
     */
    protected $timers;

    /**
     * @var \CharlotteDunois\Yasmin\Client $client Yasmin Client instance
     */
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
    public function loadTimers(): void
    {
        $this->loadCoreTimers(Discord::$config['timers']['core']);
        $this->loadPublicTimers(Discord::$config['timers']['public']);

        printf("Loading timers completed.\n");
    }

    /**
     * Setup core timers
     *
     * @param array $timers
     */
    public function loadCoreTimers(array $timers): void
    {
        foreach ($timers as $timer) {
            if (!$this->precheckTimers(self::CORE_TIMER, $timer)) {
                continue;
            }

            $config = $this->loadConfig(self::CORE_TIMER, $timer);

            if ($config === null) {
                continue;
            }

            $loadedTimer = new $timer($config);
            $this->timers->push($loadedTimer);

            $this->setTimer($loadedTimer, $config);
            printf("Completed\n");
        }
    }

    /**
     * Setup public timers
     *
     * @param array $timers
     */
    public function loadPublicTimers(array $timers): void
    {
        foreach ($timers as $timer) {
            if (!$this->precheckTimers(self::PUBLIC_TIMER, $timer)) {
                continue;
            }

            $config = $this->loadConfig(self::PUBLIC_TIMER, $timer);

            if ($config === null) {
                continue;
            }

            $loadedTimer = new $timer($config);
            $this->timers->push($loadedTimer);

            $this->setTimer($loadedTimer, $config);
            printf("Completed\n");
        }
    }

    /**
     * @param $namespace
     * @param $timer
     *
     * @return bool
     * @internal Validate a timer is correctly setup before loading
     *
     */
    private function precheckTimers($namespace, $timer): bool
    {
        $timerName = substr($timer, strlen($namespace));

        $type = ($namespace == self::CORE_TIMER) ? 'core' : 'public';

        printf("%- 50s %s", "Loading {$type} timer [{$timerName}]", ":: ");

        if (!class_exists($timer)) {
            printf("Loading failed because class %s doesn't exist.\n", $timerName);
            return false;
        }

        if (!is_subclass_of($timer, Timer::class)) {
            printf("Loading failed because class %s doesn't extend %s.\n", $timerName, Timer::class);
            return false;
        }

        return true;
    }

    /**
     * @param $namespace
     * @param $timer
     *
     * @return array|null
     * @internal Load a plugin for a timer
     *
     */
    private function loadConfig($namespace, $timer): ?array
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
     * @param Timer $timer
     * @param array $config
     * @internal Add the timer to the timer loop set by timout
     *
     */
    private function setTimer(Timer $timer, $config): void
    {
        if ($config['once'] === true) {
            $this->client->addTimer($config['interval'], [$timer, 'trigger']);
            return;
        }

        $this->client->addPeriodicTimer($config['interval'], [$timer, 'trigger']);
    }
}