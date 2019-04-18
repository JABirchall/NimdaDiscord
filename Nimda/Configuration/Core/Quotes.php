<?php


namespace Nimda\Configuration\Core;


class Quotes
{
    public static $config = [
        'trigger' => [
            'commands' => [
                'quote(s)? {action:add|search} {text:.*}',
                'quote(s)? {action:get|remove} {id:\d+}',
                'quote(s)? {action:random}',
            ]
        ]
    ];
}