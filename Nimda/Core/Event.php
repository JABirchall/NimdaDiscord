<?php

namespace Nimda\Core;

use CharlotteDunois\Yasmin\Models\GuildMember;
use React\Promise\ExtendedPromiseInterface;

/**
 * Class Event
 * @package Nimda\Core
 */
abstract class Event
{
    /**
     * @var array $config Configuration for the event object
     */
    protected $config;

    /**
     * Event constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Method for handling guildMember(Join|Leave|Updated)
     *
     * @param GuildMember $member
     * @param GuildMember|null $memberOld
     *
     * @return ExtendedPromiseInterface
     */
    abstract public function userEventTrigger(GuildMember $member, GuildMember $memberOld = null);
}