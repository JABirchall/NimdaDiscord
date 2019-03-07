<?php

namespace Nimda\Configuration;

class Discord
{
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
            ],
            /**
             * Public plugins created by the community. Nimda Team is not responsible for their functionality.
             */
            'public' => [

            ],
        ],

        'timers' => [
            'core' => [
            ],
            'public' => [
                \Nimda\Timers\Announcement::class,
            ],
        ],
    ];
}