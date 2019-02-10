<?php

namespace Nimda\Configuration\Core;


class SayHello
{
    public static $config = [
        'message' => "Hello %s",

        'trigger' => [
            'commands' =>
                [
                    '!hello',
                    '!hi',
                ]
        ],
    ];
}