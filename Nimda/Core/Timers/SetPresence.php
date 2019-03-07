<?php

namespace Nimda\Core\Timers;

use CharlotteDunois\Yasmin\Client;
use Nimda\Core\Timer;

class SetPresence extends Timer
{
    public function trigger(Client $client)
    {
        $client->user->setAvatar($this->config['avatar']);
        $client->user->setStatus($this->config['status']);
        $client->user->setGame($this->config['presence']['game']['name']);
        $client->user->setPresence($this->config['presence']);
    }
}