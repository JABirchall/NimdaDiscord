<?php  declare(strict_types=1);

namespace Nimda;

use CharlotteDunois\Yasmin\Client;
use Nimda\Configuration\Discord;
use Nimda\Core\CommandContainer;
use Nimda\Core\Conversation;
use Nimda\Core\Database;
use Nimda\Core\EventContainer;
use Nimda\Core\TimerContainer;
use React\EventLoop\Factory;

/**
 * Class Nimda
 * @package Nimda
 */
final class Nimda
{
    /**
     * @var \React\EventLoop\LoopInterface $loop
     */
    private $loop;

    /**
     * @var \CharlotteDunois\Yasmin\Client $client
     */
    private $client;

    /**
     * @var \Nimda\Core\CommandContainer $commands
     */
    private $commands;

    /**
     * @var \Nimda\Core\EventContainer $events
     */
    private $events;

    /**
     * @var \Nimda\Core\TimerContainer $timers
     */
    private $timers;

    /**
     * Nimda constructor.
     * @throws \Throwable
     */
    public function __construct()
    {
        $this->startupCheck();
        $this->loop = Factory::create();
        $this->client = new Client(Discord::$config['options'], $this->loop);
        Database::boot();

        $this->commands = new CommandContainer();
        $this->events = new EventContainer($this->client);
        $this->timers = new TimerContainer($this->client);

        $this->register();

        $this->commands->loadCommands();
        $this->events->loadEvents();
        $this->timers->loadTimers();
    }

    /**
     * Nimda run method, boots and runs the discord loop
     */
    public function run(): void
    {
        Conversation::init();
        $this->client->login(Discord::$config['client_token'])->done();
        $this->loop->run();
    }

    /**
     * Runs when a connection is established
     */
    public function onReady(): void
    {
        printf('Logged in as %s created on %s' . PHP_EOL, $this->client->user->tag,
            $this->client->user->createdAt->format('d.m.Y H:i:s')
        );

        $this->client->addPeriodicTimer(Discord::$config['conversation']['timeout'],
            [Conversation::class, 'refreshConversations']
        );
    }

    /**
     * @internal Register events for Nimda to handle
     */
    private function register(): void
    {
        $this->client->on('ready', [$this, 'onReady']);
        $this->client->on('message', [$this->commands, 'onMessage']);
    }

    /**
     * @throws \Exception & \Throwable
     * @internal Check for invalid options before booting
     *
     */
    private function startupCheck(): void
    {
        throw_if(\PHP_SAPI !== 'cli', \Exception::class, 'Nimda can only be used in the CLI SAPI. Please use PHP CLI to run Nimda.');

        throw_if(Discord::$config['client_token'] === '', \Exception::class, 'No client token set in config.');

        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' && posix_getuid() === 0) {
            printf("[WARNING] Running Nimda as root is dangerous!\nStart anyway? Y/N: ");

            $answer = strcasecmp(rtrim(fgets(STDIN)), 'y');
            throw_if($answer !== 0, \Exception::class, 'Nimda running as root, user aborted.');
        }
    }
}