<?php

namespace Nimda\Core\Timers;

use CharlotteDunois\Yasmin\Client;
use Nimda\Core\Timer;

class Announcement extends Timer
{
    /**
     * @inheritDoc
     */
    public function trigger(Client $client)
    {
        return $client->channels->get($this->config['channelId'])->send($this->config['message']);
    }
}