<?php

namespace Nimda\Configuration\Core;

class SayHello
{
    public static $config = [
        'message' => "Hello, How are you?",

        'trigger' => [
            'commands' => [
                'hello',
                'hi',
            ]
        ],
    ];
}