<?php

namespace Nimda\Core;

use CharlotteDunois\Yasmin\Models\Message;
use CharlotteDunois\Yasmin\Models\GuildMember;

/**
 * Class Command
 * @package Nimda\Core
 */
abstract class Command
{
    /**
     * @var array $config Configuration for the object
     */
    protected $config;

    /**
     * Command constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Command trigger method triggered when a valid command has been matched.
     *
     * @param Message $message
     * @param array $args
     *
     * @return mixed
     */
    abstract public function trigger(Message $message, array $args = []);

    /**
     * Middleware is triggered before the command is ran to check authorization.
     * This method must be overridden by the commands specific middleware.
     *
     * @override
     * @param GuildMember $author
     *
     * @return bool|true
     */
    public function middleware(GuildMember $author): bool
    {
        return true;
    }
}