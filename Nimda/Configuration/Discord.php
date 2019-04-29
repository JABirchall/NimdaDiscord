<?php

namespace Nimda\Configuration;

/**
 * Class Discord
 * @package Nimda\Configuration
 */
class Discord
{
    /**
     * @var array $config Nimda master configuration
     */
    public static $config = [
        /**
         * Discord API configuration
         */
        'client_token' => '',
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
            'ws.disabledEvents' => [],
        ],
        /**
         * Command prefix, change this to what ever you wish (Note: / @ is reserved and interpreted by Discord)
         */
        'prefix' => '!',
        'deleteCommands' => false,

        'conversation' => [
            'safeword' => '!cancel',
            'timeout' => 10, //Timeout in minutes, after this time stale conversations will be removed.
        ],

        'commands' => [
            /**
             * Core commands provided with Nimda with basic fundamental features
             */
            'core' => [
                # \Nimda\Core\Commands\MessageLogger::class,
                \Nimda\Core\Commands\SayHello::class,
                \Nimda\Core\Commands\PurgeChat::class,
                \Nimda\Core\Commands\Dice::class,
                \Nimda\Core\Commands\Quotes::class,
            ],
            /**
             * Public commands created by the community. The Nimda Team are not responsible for their functionality.
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

            ]
        ]
    ];
}