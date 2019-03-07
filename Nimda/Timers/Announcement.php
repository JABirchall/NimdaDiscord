<?php
/**
 * Created by PhpStorm.
 * User: Jake
 * Date: 07/03/2019
 * Time: 13:12
 */

namespace Nimda\Timers;


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