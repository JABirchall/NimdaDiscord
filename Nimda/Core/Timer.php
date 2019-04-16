<?php

namespace Nimda\Core;

use CharlotteDunois\Yasmin\Client;
use React\Promise\ExtendedPromiseInterface;

/**
 * Class Timer
 * @package Nimda\Core
 */
abstract class Timer
{
    /**
     * @var array $config Configuration for the object
     */
    protected $config;

    /**
     * Timer constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Timer trigger method
     *
     * @param Client $client
     *
     * @return ExtendedPromiseInterface
     */
    abstract public function trigger(Client $client);
}