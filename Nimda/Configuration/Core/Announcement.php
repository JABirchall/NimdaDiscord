<?php

namespace Nimda\Configuration\Core;

class Announcement
{
    public static $config = [
        'message' => "I'm a core announcement in configured in " . __FILE__,
        'interval' => 120,
        'once' => false,
        'channelId' => '544112985980010498',
    ];
}