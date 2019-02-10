<?php

namespace Nimda;

use CharlotteDunois\Yasmin\Client;
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
        $this->options = $options;
        $this->loop = Factory::create();

    }

    public function newInstance()
    {
        try {
            $this->client = new Client($this->options['options'], $this->loop);
            $this->client->login($this->options['client_token'])->done();

            $this->client->user->setAvatar($this->options['avatar']);
            $this->client->user->setStatus($this->options['status']);

            $this->client->user->setGame('Yasmin');
            $this->client->user->setPresence($this->options['presence']);
        } catch ( \Exception $e) {
            printf($e->getMessage());
        }
    }

    public function run()
    {
        $this->loop->run();
    }

}