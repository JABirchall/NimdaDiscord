<?php

namespace Nimda\Configuration\Core;

class SetPresence
{
    public static $config = [
        'avatar' => 'https://i.imgur.com/DVkx8B9.jpg',
        'status' => 'online',
        'presence' => [
            'game' => [
                'name' => 'Nimda',
                'type' => 0
            ]
        ],
        'interval' => '5',
        'once' => true,
    ];
}