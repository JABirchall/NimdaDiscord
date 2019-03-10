<?php

namespace Nimda\Configuration\Core;


class PurgeChat
{
    public static $config = [
        'default' => 10,

        'trigger' => [
            'commands' => [
                'purge',
            ]
        ],
    ];
}