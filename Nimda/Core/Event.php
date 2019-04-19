<?php declare(strict_types=1);

namespace Nimda\Core;

use CharlotteDunois\Yasmin\Models\GuildMember;
use React\Promise\PromiseInterface;

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
     * @return PromiseInterface
     */
    abstract public function userEventTrigger(GuildMember $member, GuildMember $memberOld = null): PromiseInterface;
}