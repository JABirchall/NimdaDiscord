<?php

namespace Nimda\Core\Commands;

use CharlotteDunois\Yasmin\Models\Message;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Nimda\Core\Command;
use React\Promise\PromiseInterface;

class Dice extends Command
{
    public function trigger(Message $message, Collection $args = null): PromiseInterface
    {
        $sides = $args->get('sides', $this->config['default']['sides']);
        $dice = $args->get('dice', $this->config['default']['dice']);

        if ($sides < 2) {
            return $message->reply("You can not roll dice with 1 side, what are you, stupid?")->then(function (Message $message) {
                return $message->delete(10);
            });
        } else if ($dice < 1) {
            return $message->reply("You can not roll zero die, what are you, stupid?")->then(function (Message $message) {
                return $message->delete(10);
            });
        }

        $result = Collection::make(array_fill(0, $dice, $sides))->map(function ($item) {
            return random_int(1, $item);
        })->implode(', ');

        $response = "You roll {$dice} x {$sides}-sided " . ($dice < 2 ? 'die' : 'dice') . ". ";

        return $message->reply($response)->then(function (Message $message) use ($dice, $result) {
            return $message->client->addTimer(random_int($dice, $dice * 3), function () use ($message, $result) {
                $response = Str::replaceFirst('roll', 'rolled', $message->content);
                return $message->edit($response . ' Dice Results: ' . $result);
            });
        });
    }

    public function isConfigured(): bool
    {
        return !(empty($this->config['default']['dice']) ||
            empty($this->config['default']['sides']) ||
            $this->config['default']['dice'] < 1 ||
            $this->config['default']['sides'] < 2);
    }
}
