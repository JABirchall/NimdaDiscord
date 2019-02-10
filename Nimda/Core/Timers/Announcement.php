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
        $channel = $client->guilds->get('544112985980010496')->channels->get('544112985980010498');
        $channel->send($this->config['message']);
    }
}