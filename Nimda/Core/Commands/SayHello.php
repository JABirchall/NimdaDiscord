<?php

namespace Nimda\Core\Commands;

use CharlotteDunois\Yasmin\Models\Message;
use Illuminate\Support\Collection;
use Nimda\Core\Command;

class SayHello extends Command
{
    /**
     * @inheritDoc
     */
    public function trigger(Message $message, Collection $args = null)
    {
        $message->reply($this->config['message']);
    }
}