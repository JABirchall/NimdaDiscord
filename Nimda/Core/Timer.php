<?php

namespace Nimda\Core;

use CharlotteDunois\Yasmin\Client;

abstract class Timer
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    abstract public function trigger(Client $client);
}