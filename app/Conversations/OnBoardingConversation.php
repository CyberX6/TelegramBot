<?php

namespace App\Conversations;

use App\Models\Client;
use BotMan\BotMan\Facades\BotMan;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;

class OnBoardingConversation extends Conversation
{
    protected $clientId;
    protected $clientName;
    protected $clientAge;
    /**
     * First question
     */

    /**
     * Start the conversation
     */
    public function run()
    {
        $this->clientId = $this->saveNewClient();

        $this->askClientName();
    }

    public function saveNewClient()
    {
        $botman = resolve('botman');
        $id = $botman->getUser()->getId();

        return Client::create(['user_id' => $id])->id;
    }

    protected function askClientName($internal = false)
    {
        $this->ask($internal ?
            'Okay, so you didn\'t like my suggestion, I pretend I am not hurt, enter your version and
            I will try to learn it 😏'
            :
            'Hey cutie 😜, May I know your name?', function (Answer $answer) {
                $clientName = trim($answer->getText());

                try {
                    \Validator::make(['name' => $clientName], [
                    'name' => 'required|string|min:5|max:255',
                    ])->validate();
                } catch (\Exception $e) {
                    $this->repeat('I really don\'t want to think you are avoiding me from knowing your name 🥸,
                I am giving you another chance because I am that kind ☺️');
                }

                if (preg_match("/\p{Cyrillic}/u", $clientName, $match)) {
                    $this->saveClientName($clientName);
                } else {
                    $this->say('Sorry ' . $clientName . ', I only read cyrillic characters, but don\'t worry dear,
                I will do your job (did I already admit that I have crush on you?),
                just check my guess, your cyrillic name looks like this: ' .
                    $this->convertToCyrillic($clientName));

                    $question = Question::create('Am I right 😗?')
                    ->addButtons([
                        Button::create('Sure, as always')->value($this->convertToCyrillic($clientName)),
                        Button::create('No, you stupid robot')->value('no'),
                    ]);

                    $this->ask($question, function (Answer $answer) {
                        if ($answer->isInteractiveMessageReply()) {
                            if ($answer->getValue() !== 'no') {
                                $this->saveClientName($answer->getValue());
                            } else {
                                $this->say('Well, you could have been nicer... 🙄');
                                $this->askClientName(true);
                            }
                        } else {
                            $this->askClientName(true);
                        }
                    });
                }
            });
    }

    protected function saveClientName($name): void
    {
        $this->clientName = $name;
        $this->say('Привет, ' . $this->clientName . ' 😍!');

        $this->askAge();
    }

    protected function askAge()
    {
        $this->ask('Now dear ' . $this->clientName . ', just tell me your age to find out if you
        are a perfect match 🤩', function (Answer $answer) {
            try {
                \Validator::make(['age' => $answer->getText()], ['age' => 'required|integer|min:13|max:67'])
                    ->validate();

                $this->say('Of course 🥳, ' . $answer->getText() . ' is just right!');
                $this->clientAge = $answer->getText();
                $this->saveClient();
            } catch (\Exception $e) {
                $this->repeat('Come on, ' . $this->clientName . ', I already have a depression 😩');
            }
        });
    }

    protected function saveClient()
    {
        $client = Client::find($this->clientId);
        $client->name = $this->clientName;
        $client->age = $this->clientAge;
        $client->status = config('clients.STATUS_IDS.ACTIVE');

        $client->save();

        $this->finishConversation();
    }

    protected function finishConversation()
    {
        $this->say('That\'s all for now! I forgot that I am introvert like my creator
         and it\'s enough for today 🤦 I hope you will come back soon 😉');
        $this->say('Bye bye ' . $this->clientName . '! I will miss you 😢');
    }

    /**
     * Convert latin string to cyrillic
     * @param  string  $str
     * @return string
     */
    public function convertToCyrillic(string $str): string
    {
        $cyr = [
            'ё',  'ж',  'х',  'ц',  'ч',  'щ',   'ш',  'ъ',  'э',  'ю',  'я',  'а', 'б', 'в', 'г', 'д',
            'е', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'ь', 'Ё',  'Ж',
            'Х',  'Ц',  'Ч',  'Щ',   'Ш',  'Ъ',  'Э',  'Ю',  'Я',  'А', 'Б', 'В', 'Г', 'Д', 'Е', 'З',
            'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Ь'];
        $lat = [
            'yo', 'zh', 'kh', 'ts', 'ch', 'shh', 'sh', '``', 'eh', 'yu', 'ya', 'a', 'b', 'v', 'g', 'd', 'e',
            'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', '`', 'Yo', 'Zh', 'Kh', 'Ts',
            'Ch', 'Shh', 'Sh', '``', 'Eh', 'Yu', 'Ya', 'A', 'B', 'V', 'G', 'D', 'E', 'Z', 'I', 'J', 'K', 'L',
            'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', '`'];

        return str_replace($lat, $cyr, $str);
    }
}
