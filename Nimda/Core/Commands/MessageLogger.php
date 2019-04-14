<?php

namespace Nimda\Core\Commands;

use CharlotteDunois\Yasmin\Models\Message;
use Illuminate\Support\Collection;
use Nimda\Core\Command;

class MessageLogger extends Command
{
    /**
     * @inheritDoc
     */
    public function trigger(Message $message, Collection $args = null)
    {
        // TODO: Implement trigger() method.
    }
}