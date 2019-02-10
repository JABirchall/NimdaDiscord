<?php
/**
 * Created by PhpStorm.
 * User: Jake
 * Date: 10/02/2019
 * Time: 14:33
 */

namespace Nimda\Configuration\Core;


class Announcement
{
    public static $config = [
        'message' => "I'm an announcement in configured in ". __FILE__,
        'interval' => 10,
        'once' => false
    ];
}