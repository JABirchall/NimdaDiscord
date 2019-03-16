<?php

namespace Nimda\Core;

use CharlotteDunois\Yasmin\Client;

/**
 * Class Timer
 * @package Nimda\Core
 */
abstract class Timer
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Timer constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Timer trigger method
     * @param Client $client
     * @return mixed
     */
    abstract public function trigger(Client $client);
}