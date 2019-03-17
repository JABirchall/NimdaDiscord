<?php

namespace Nimda\Core;

use CharlotteDunois\Yasmin\Models\GuildMember;

/**
 * Class UserEvent
 * @package Nimda\Core
 */
abstract class UserEvent
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
     * @param GuildMember $member
     * @param GuildMember|null $memberOld
     * @return mixed
     */
    abstract public function userEventTrigger(GuildMember $member, GuildMember $memberOld = null);
}