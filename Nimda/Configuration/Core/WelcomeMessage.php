<?php

namespace Nimda\Configuration\Core;

class WelcomeMessage
{
    public static $config = [
        'enabled' => true,
        'channel' => '544112985980010498',
        'embed' => true,
        'mention' => false,
        'trigger' => 'guildMemberAdd',
    ];
}