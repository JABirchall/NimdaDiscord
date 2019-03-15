<?php

namespace Nimda\Core;

use CharlotteDunois\Yasmin\Models\Message;

abstract class Plugin
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    abstract public function trigger(Message $message, array $args = []);
}