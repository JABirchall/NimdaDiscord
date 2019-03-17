<?php

namespace Nimda\Core\Timers;

use CharlotteDunois\Yasmin\Client;
use Nimda\Core\Timer;

class SetPresence extends Timer
{
    /**
     * @inheritDoc
     */
    public function trigger(Client $client)
    {
        $client->user->setAvatar($this->config['avatar']);
        $client->user->setPresence($this->config['presence']);
    }
}