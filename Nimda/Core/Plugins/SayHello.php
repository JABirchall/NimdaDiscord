<?php

namespace Nimda\Core\Plugins;

use CharlotteDunois\Yasmin\Models\Message;

class SayHello
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function trigger(Message $message)
    {
        $message->reply("Hello.");
    }
}