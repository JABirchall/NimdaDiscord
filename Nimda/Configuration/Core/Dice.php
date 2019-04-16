<?php

namespace Nimda\Configuration\Core;

class Dice
{
    public static $config = [
        'default' => [
            'dice' => 2,
            'sides' => 6,
        ],
        'trigger' => [
            'commands' => [
                'roll {sides:\d+}? {dice:\d+}?'
            ]
        ]
    ];
}
