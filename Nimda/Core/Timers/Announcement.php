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
        /* @var \CharlotteDunois\Yasmin\Models\TextChannel $channel */
        $channel = $client->guilds->get($this->config['guildId'])->channels->get($this->config['channelId']);
        $channel->send($this->config['message']);
    }
}