<?php

namespace Nimda\Configuration;

/**
 * Class Discord
 * @package Nimda\Configuration
 */
class Discord
{
    /**
     * Nimda master configuration
     * @var array $config
     */
    public static $config = [
        /**
         * Discord API configuration
         */
        'client_token' => 'NTQ0MTAxMDE5NDE2Nzg5MDA5.D2K6DA.uv-qDRR9LJv1qptwT5HEF78EqD0',
        'permissions' => 8,
        'name' => 'Nimda',
        'options' => [
            'disableClones' => true,
            'disableEveryone' => true,
            'fetchAllMembers' => false,
            'messageCache' => true,
            'messageCacheLifetime' => 600,
            'messageSweepInterval' => 600,
            'presenceCache' => false,
            'userSweepInterval' => 600,
            'ws.disabledEvents' => ['TYPING_START'], // Do not remove TYPING_START
        ],
        /**
         * Command prefix, change this to what ever you wish (Note: / @ is reserved and interpreted by Discord)
         */
        'prefix' => '!',
        'plugins' => [
            /**
             * Core plugins provided with Nimda with basic fundamental features
             */
            'core' => [
                \Nimda\Core\Plugins\MessageLogger::class,
                \Nimda\Core\Plugins\SayHello::class,
                \Nimda\Core\Plugins\PurgeChat::class,
            ],
            /**
             * Public plugins created by the community. The Nimda Team are not responsible for their functionality.
             */
            'public' => [

            ],
        ],

        'timers' => [
            'core' => [
                \Nimda\Core\Timers\Announcement::class,
                \Nimda\Core\Timers\SetPresence::class,
            ],
            'public' => [

            ],
        ],

        'events' => [
            /**
             * Core events provided with Nimda.
             */
            'core' => [
                \Nimda\Core\Events\WelcomeMessage::class,
            ],
            /**
             * Public events created by the community. The Nimda Team are not responsible for their functionality.
             */
            'public' => [
                \Nimda\Events\LeaveMessage::class,
            ]
        ]
    ];
}