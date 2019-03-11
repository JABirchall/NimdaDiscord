<?php

namespace Nimda;

use CharlotteDunois\Yasmin\Client;
use Nimda\Configuration\Discord;
use Nimda\Core\PluginContainer;
use Nimda\Core\TimerContainer;
use React\EventLoop\Factory;

final class Nimda
{
    private $loop;

    private $client;

    private $plugins;

    private $timers;

    public function __construct()
    {
        $this->startupCheck();
        $this->loop = Factory::create();
        $this->client = new Client(Discord::$config['options'], $this->loop);

        $this->plugins = new PluginContainer();
        $this->plugins->loadPlugins(Discord::$config['plugins']);

        $this->timers = new TimerContainer();
        $this->timers->loadTimers($this->client, Discord::$config['timers']);

        $this->register();
    }

    public function run()
    {
        $this->client->login(Discord::$config['client_token'])->done();
        $this->loop->run();
    }

    public function onReady() {
        printf('Logged in as %s created on %s'.PHP_EOL, $this->client->user->tag, $this->client->user->createdAt->format('d.m.Y H:i:s'));
    }

    private function register()
    {
        $this->client->on('message', [$this->plugins, 'onMessage']);
        $this->client->on('ready', [$this, 'onReady']);
    }

    private function startupCheck()
    {
        if(\PHP_SAPI !== 'cli') {
            throw new \Exception('Nimda can only be used in the CLI SAPI. Please use PHP CLI to run Nimda.');
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' && posix_getuid() === 0) {
            printf("[WARNING] Running Nimda as root is dangerous!\nStart anyway? Y/N: ");

            if (strcasecmp(rtrim(fgets(STDIN)),'y')) {
                throw new \Exception('Nimda running as root, user aborted.');
            }
        }
    }
}