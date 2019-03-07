<?php

namespace Nimda\Timers\Configuration;

class Announcement
{
    public static $config = [
        'message' => "I'm a public announcement in configured in ". __FILE__,
        'interval' => 10,
        'once' => false,
        'guildId' => '544112985980010496',
        'channelId' => '544112985980010498',

    ];
}