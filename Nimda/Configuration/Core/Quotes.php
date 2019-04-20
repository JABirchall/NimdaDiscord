<?php

namespace Nimda\Configuration\Core;

class Quotes
{
    public static $config = [
        'trigger' => [
            'commands' => [
                'quote(s)? {action:add|search|find} {text:.*}',
                'quote(s)? {action:get|remove|delete} {id:\d+}',
                'quote(s)? {action:random}',
            ]
        ]
    ];
}