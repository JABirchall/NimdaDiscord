<?php

namespace Nimda\Configuration\Core;


class SayHello
{
    public static $config = [
        'message' => "Hello, it's me",

        'trigger' => [
            'commands' =>
                [
                    'hello',
                    'hi',
                ]
        ],
    ];
}