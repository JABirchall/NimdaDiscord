<?php

namespace Nimda\Core\Timers;

use CharlotteDunois\Yasmin\Client;

class Announcement
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function trigger(Client $client)
    {
        $channel = $client->guilds->get($this->config['guildId'])->channels->get($this->config['channelId']);
        $channel->send($this->config['message']);
    }
}