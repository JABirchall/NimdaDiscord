<?php

namespace Nimda;

use CharlotteDunois\Yasmin\Client;
use Nimda\Configuration\Discord;
use Nimda\Core\CommandContainer;
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

        $this->commands = new CommandContainer();
        $this->commands->loadCommands();

        $this->timers = new TimerContainer($this->client);
        $this->timers->loadTimers();

        $this->events = new EventContainer();
        $this->events->loadEvents();

        $this->register();

        Database::boot();
    }

    /**
     * Nimda run method, boots and runs the discord loop
     */
    public function run()
    {
        $this->client->login(Discord::$config['client_token'])->done();
        $this->loop->run();
    }

    /**
     * Runs when a connection is established
     */
    public function onReady()
    {
        printf('Logged in as %s created on %s' . PHP_EOL, $this->client->user->tag,
            $this->client->user->createdAt->format('d.m.Y H:i:s'));
    }

    /**
     * @internal Register events for Nimda to handle
     */
    private function register()
    {
        $this->client->on('message', [$this->commands, 'onMessage']);
        $this->client->on('ready', [$this, 'onReady']);
        $this->client->on("guildMemberAdd", [$this->events, 'guildMemberAdd']);
    }

    /**
     * @throws \Exception & \Throwable
     * @internal Check for invalid options before booting
     *
     */
    private function startupCheck()
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