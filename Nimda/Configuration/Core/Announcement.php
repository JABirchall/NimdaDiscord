<?php

namespace Nimda\Configuration\Core;


class Announcement
{
    public static $config = [
        'message' => "I'm an announcement in configured in ". __FILE__,
        'interval' => 10,
        'once' => false
    ];
}