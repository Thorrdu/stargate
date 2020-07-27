<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\DiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use App\Player;
use App\Building;
use App\Technology;
use Carbon\Carbon;


class Paginator extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            if($this->player->ban)
                return 'Vous êtes banni...';


            try{
                $number = 1;
                $this->message->channel->sendMessage($this->getTop($number))->then(function ($messageSent) use($number){
                    try{
                    $number++;
                    $messageSent->channel->sendMessage($number);
                    $messageSent->edit($number);
                    //editMessage

                    //messageSent->edit();
                    //$this->close();
                    }
                    catch(\Exception $e)
                    {
                        echo $e->getMessage();
                        return $e->getMessage();
                    }

                }, function ($e) {
                   echo $e->getMessage();
                   return $e->getMessage();
                });

                //$this->discord->updatePresence($game);


            }
            catch(\Exception $e)
            {
                echo $e->getMessage();
                return $e->getMessage();
            }


   
        }
        else
            return "Pour commencer votre aventure, utilisez `!start`";
        return false;
    }

    public function getTop($number)
    {
        return 'Page '.$number;
    }
}
