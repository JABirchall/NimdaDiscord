<?php

namespace Nimda\Core\Plugins;

use CharlotteDunois\Yasmin\Models\Message;
use Nimda\Core\Plugin;

class MessageLogger extends Plugin
{
    public function trigger(Message $message, array $args = [])
    {
        // TODO: Implement trigger() method.
    }
}