<?php

namespace App\Commands;

use App\Player;
use Discord\Parts\Embed\Embed;
use Illuminate\Support\Facades\DB;

class Start extends CommandHandler implements CommandInterface
{
    public $maxTime;
    public $paginatorMessage;
    public $listner;
    public $buildingList;
    public $newPlayerId;
    public $userName;
    public $messageReaction;

    public function execute()
    {
        echo PHP_EOL.'Execute Start ';
        if(is_null($this->player))
        {
            try{

                if(isset($this->message->author->user))
                    $this->userName = $this->message->author->user->username;
                else
                    $this->userName = $this->message->author->username;

                $this->newPlayerId = $this->message->author->id;
                $this->maxTime = time()+180;
                $embed = [
                    'author' => [
                        'name' => "Stargate",
                        'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                    ],
                    "title" => $this->userName,
                    "description" => trans('start.langChoice',[],'en')."\n\n".trans('start.langChoice',[],'fr'),
                    'fields' => [],
                    'footer' => array(
                        'text'  => 'Stargate',
                    )
                ];
                $newEmbed = $this->discord->factory(Embed::class,$embed);

                $this->message->channel->sendMessage('',false, $newEmbed)->then(function ($messageSent){
                    $this->paginatorMessage = $messageSent;
                    $this->paginatorMessage->react('🇬🇧')->then(function(){
                        $this->paginatorMessage->react('🇫🇷');
                    });

                    $this->listner = function ($messageReaction) {
                        if($this->maxTime < time())
                            $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);

                        if($messageReaction->message->id == $this->paginatorMessage->id && $messageReaction->user_id == $this->message->author->id)
                        {
                            $this->messageReaction = $messageReaction;

                            if($messageReaction->emoji->name == '🇫🇷')
                                $this->start('fr');
                            elseif($messageReaction->emoji->name == '🇬🇧')
                                $this->start('en');
                        }
                    };
                    $this->discord->on('MESSAGE_REACTION_ADD', $this->listner);

                });
            }
            catch(\Exception $e)
            {
                return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
            }
        }
        elseif($this->player->ban)
            return trans('generic.banned',[],$this->player->lang);
        elseif($this->player->captcha)
            return trans('generic.captchaMessage',[],$this->player->lang);
        else
            return trans('start.accountExists',[],$this->player->lang);
    }

    public function start($lang)
    {
        try{
            $newPlayer = new Player;
            $newPlayer->user_id = $this->newPlayerId;
            $newPlayer->user_name = $this->userName;
            $newPlayer->ban = false;
            $newPlayer->lang = $lang;
            $newPlayer->votes = 0;
            $newPlayer->save();
            $newPlayer->addColony();

            $embed = [
                'author' => [
                    'name' => $newPlayer->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                ],
                "title" => "Welcome to Stargate",
                "description" => trans('start.startMessage',[],$newPlayer->lang),
                'fields' => [],
                'footer' => array(
                    'text'  => 'Stargate',
                )
            ];
            $newEmbed = $this->discord->factory(Embed::class,$embed);
            $this->paginatorMessage->addEmbed($newEmbed);
            $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);

            DB::table('ships')->insert([
                'name' => "F-302",
                'slug' => 'f302',
                'player_id' => $newPlayer->id,
                'required_shipyard' => 1,
                'required_blueprint' => 1,
                'iron' => 19300,
                'gold' => 18800,
                'quartz' => 1420,
                'naqahdah' => 5,
                'base_time' => 700,
                'capacity' => 630,
                'crew' => 2,
                'fire_power' => 150,
                'shield' => 800,
                'hull' => 1000,
                'speed' => 0.5,
            ]);

            DB::table('ships')->insert([
                'name' => "Tel'tak",
                'slug' => 'teltak',
                'player_id' => $newPlayer->id,
                'required_shipyard' => 4,
                'required_blueprint' => 5,
                'iron' => 36700,
                'gold' => 36200,
                'quartz' => 1600,
                'naqahdah' => 20,
                'base_time' => 1680,
                'crew' => 100,
                'capacity' => 16000,
                'fire_power' => 200,
                'shield' => 2200,
                'hull' => 3000,
                'speed' => 1,
            ]);

            DB::table('ships')->insert([
                'name' => "Al'kesh",
                'slug' => 'alkesh',
                'player_id' => $newPlayer->id,
                'required_shipyard' => 6,
                'required_blueprint' => 7,
                'iron' => 180900,
                'gold' => 180200,
                'quartz' => 8800,
                'naqahdah' => 50,
                'base_time' => 3300,
                'crew' => 500,
                'capacity' => 33180,
                'fire_power' => 2500,
                'shield' => 3000,
                'hull' => 4000,
                'speed' => 1.5,
            ]);

            DB::table('ships')->insert([
                'name' => "Prometheus",
                'slug' => 'prometheus',
                'player_id' => $newPlayer->id,
                'required_shipyard' => 8,
                'required_blueprint' => 9,
                'iron' => 1340000,
                'gold' => 1339000,
                'quartz' => 250000,
                'naqahdah' => 20000,
                'base_time' => 72000,
                'crew' => 1000,
                'capacity' => 63500,
                'fire_power' => 21000,
                'shield' => 40000,
                'hull' => 39000,
                'speed' => 3,
            ]);



        }
        catch(\Exception $e)
        {
            return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
        }
    }
}
