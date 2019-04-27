<?php

namespace Nimda\Configuration\Core;

class WelcomeMessage
{
    public static $config = [
        'channel' => '',
        'embed' => true,
        'mention' => false,
        'trigger' => [
            'events' => [
                'guildMemberAdd',
            ],
        ],
    ];
}