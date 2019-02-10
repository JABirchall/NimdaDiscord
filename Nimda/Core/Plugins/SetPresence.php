<?php

namespace Nimda\Core\Plugins;

use CharlotteDunois\Yasmin\Client;

class SetPresence
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function trigger(Client $client)
    {
        $client->user->setAvatar($this->config['avatar']);
        $client->user->setStatus($this->config['status']);
        $client->user->setGame($this->config['presence']['game']['name']);
        $client->user->setPresence($this->config['presence']);
    }
}