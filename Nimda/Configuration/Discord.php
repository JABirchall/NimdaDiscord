<?php

namespace Nimda\Configuration;

class Discord
{
    public static $config = [
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
            'ws.disabledEvents' => ['TYPING_START'],
        ],
        'plugins' => [
            \Nimda\Core\Plugins\MessageLogger::class,
            \Nimda\Core\Plugins\SayHello::class,
        ],
        'timers' => [
            \Nimda\Core\Timers\SetPresence::class,
            \Nimda\Core\Timers\Announcement::class,
        ]
    ];
}