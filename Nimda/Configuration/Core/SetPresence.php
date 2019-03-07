<?php

namespace Nimda\Configuration\Core;

class SetPresence
{
    public static $config = [
        'avatar' => 'https://i.imgur.com/DVkx8B9.jpg',
        'presence' => [
            'status' => 'online',
            'game' => [
                'name' => 'Nimda',
                'type' => 1
            ]
        ],
        'interval' => '5',
        'once' => true,
    ];
}