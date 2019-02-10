<?php

namespace Nimda\Configuration;

class Discord
{
    public static $config = [
        'client_token' => 'NTQ0MTAxMDE5NDE2Nzg5MDA5.D0GOpw.9SFcgfbzDdURIUFNQ4tCBk2eGlk',
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
            \Nimda\Core\Plugins\SetPresence::class,
            \Nimda\Core\Plugins\MessageLogger::class,
            \Nimda\Core\Plugins\SayHello::class,
        ],
    ];
}