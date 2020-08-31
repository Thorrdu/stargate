<?php

namespace App\Commands;

use App\Player;
use \Discord\Repository\UserRepository;

class Ban extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            if($this->message->author->id == 125641223544373248)
            {
                echo PHP_EOL.'Ban';
                $playerToBan = null;

                if(count($this->args) > 0 && preg_match("/[0-9]{18}/", $this->args[0], $playerMatch))
                    $playerToBan = Player::where('user_id', $playerMatch[0])->first();

                if(!is_null($playerToBan))
                {
                    if($playerToBan->ban)
                    {
                        $playerToBan->ban = false;
                        $playerToBan->save();
                        return trans('ban.banLift', ['name' => $playerToBan->user_name], $this->player->lang);
                    }
                    else
                    {
                        echo PHP_EOL.'IDPL '.$playerToBan->id;

                        try{
                            echo PHP_EOL.'IDPL '.$playerToBan->user_id;
                            var_dump($this->discord);
                            //$userExist = $this->discord->users->get('id', 125641223544373248);
                            
                            //$userExist = $this->message->channel->guild->members->get('id',$playerToBan->user_id);
                            //var_dump($userExist);

                            $playerToBan->ban = true;
                            $playerToBan->save();
                        }catch(\Exception $e)
                        {
                            echo $e->getMessage();
                        }
                        
                        $userExist->sendMessage("coucou");
                        return;
                        $userExist = $this->discord->users->filter(function ($value) use($playerToBan){
                            return $value->id == $playerToBan->user_id;
                        });
                        if($userExist->count() > 0)
                        {
                            $foundUser = $userExist->first();
                            $foundUser->sendMessage("**Anti-Cheat System**\n\nSuite à un comportement violant les règles d'utilisation du bot, vous êtes désormais banni.");
                        }


                        return trans('ban.banApplied', ['name' => $this->message->mentions[0]->username], $this->player->lang);


                    }
                }
                else
                    return trans('generic.unknownPlayer', [], $this->player->lang);
            }
            else
                return trans('generic.missingPerm', [], $this->player->lang);
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
    }
}
