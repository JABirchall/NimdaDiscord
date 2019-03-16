<?php

namespace Nimda\Core;

use CharlotteDunois\Yasmin\Models\Message;

/**
 * Class Plugin
 * @package Nimda\Core
 */
abstract class Plugin
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Plugin constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Plugin trigger method
     * @param Message $message
     * @param array $args
     * @return mixed
     */
    abstract public function trigger(Message $message, array $args = []);
}