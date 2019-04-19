<?php


namespace Nimda\Core\Commands;

use CharlotteDunois\Yasmin\Models\Message;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Nimda\Core\Command;
use Nimda\DB;
use React\Promise\PromiseInterface;

class Quotes extends Command
{
    const TABLE = 'quotes';

    public function trigger(Message $message, Collection $args = null): PromiseInterface
    {
        switch ($args->get('action')) {
            case 'add':
                    return $this->addQuote($message, $args->get('text'));
                break;

            case 'get':
                return $this->getQuote($message, $args->get('id'));
                break;

            case 'search':
                return $this->searchQuotes($message, $args->get('text'));
                break;

            case 'remove':
                return $this->removeQuote($message, $args->get('id'));
                break;

            case 'random':
                return $this->randomQuote($message);
                break;
        }
        return null;
    }

    private function addQuote(Message $message, $text)
    {
        DB::table(self::TABLE)->insert([
            'userid' => $message->author->id, 'text' => $text
        ]);

        $result = DB::table(self::TABLE)
            ->where('userid', $message->author->id)
            ->where('text', $text)
            ->first();

        if ($result) {
            return $message->reply("Succesfully added quote id: {$result->id}\nQuote: \"{$result->text}\"");
        }
        return $message->reply("I have a problem adding this quote...");
    }

    private function getQuote(Message $message, $id)
    {
        $result = DB::table(self::TABLE)->where('id', $id)->first();

        if ($result) {
            return $message->reply("Quote id: {$result->id}\nQuote: \"{$result->text}\"");
        }
        return $message->reply("No quote exist by that ID");
    }

    private function searchQuotes(Message $message, $text)
    {
        $results = DB::table(self::TABLE)->where('text', 'LIKE', '%'.$text.'%')->pluck('id');

        if($results->isNotEmpty()){
            $ids = $results->implode(', ');
            return $message->reply("I have found {$results->count()} quotes matching your query.\n{$ids}");
        }
        return $message->reply("My search came up empty");
    }

    private function removeQuote(Message $message, $id)
    {
        $result = DB::table(self::TABLE)->delete($id);
        if ($result) {
            return $message->reply("Quote id: {$id} successfully deleted.");
        }
        return $message->reply("I can not remove what does not exist in the first place.");
    }

    private function randomQuote(Message $message)
    {
        $random = DB::table(self::TABLE)->inRandomOrder()->first();

        return $message->reply("Random quote id: {$random->id}\n {$random->text}");
    }

    public function install()
    {
        if(!DB::schema()->hasTable(self::TABLE)){
            DB::schema()->create(self::TABLE, function (Blueprint $table){
                $table->increments('id');
                $table->string('userid');
                $table->string('text');
                $table->timestamps();
            });
        }
    }
}