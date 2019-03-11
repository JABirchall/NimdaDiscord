<?php

namespace Nimda\Core\Plugins;

use CharlotteDunois\Yasmin\Models\Message;
use Nimda\Core\Plugin;

class SayHello extends Plugin
{
    public function trigger(Message $message, $text = null)
    {
        $message->reply($this->config['message']);
    }
}