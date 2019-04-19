<?php

namespace Nimda\Core\Timers;

use CharlotteDunois\Yasmin\Client;
use Nimda\Core\Timer;
use React\Promise\PromiseInterface;

class SetPresence extends Timer
{
    /**
     * @inheritDoc
     */
    public function trigger(Client $client): PromiseInterface
    {
        return $client->user->setAvatar($this->config['avatar'])->then(function () use ($client) {
            return $client->user->setPresence($this->config['presence']);
        });
    }
}