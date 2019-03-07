<?php

namespace Nimda;

use CharlotteDunois\Yasmin\Client;
use Nimda\Core\PluginContainer;
use Nimda\Core\TimerContainer;
use React\EventLoop\Factory;

final class Nimda
{
    private $loop;

    private $client;

    private $options;

    private $plugins;

    private $timers;

    public function __construct(array $options)
    {
        if(\PHP_SAPI !== 'cli') {
            throw new \Exception('Nimda can only be used in the CLI SAPI. Please use PHP CLI to run Nimda.');
        }

        $this->options = $options;
        $this->loop = Factory::create();
        $this->client = new Client($this->options['options'], $this->loop);

        $this->plugins = new PluginContainer();
        $this->plugins->loadPlugins($this->options['plugins']);

        $this->timers = new TimerContainer();
        $this->timers->loadTimers($this->client, $this->options['timers']);

        $this->register();
    }

    public function run()
    {
        $this->client->login($this->options['client_token'])->done();
        $this->loop->run();
    }

    public function onReady() {
        printf('Logged in as %s created on %s'.PHP_EOL, $this->client->user->tag, $this->client->user->createdAt->format('d.m.Y H:i:s'));
    }

    public function register()
    {
        $this->client->on('message', [$this->plugins, 'onMessage']);
        $this->client->on('ready', [$this, 'onReady']);
    }
}