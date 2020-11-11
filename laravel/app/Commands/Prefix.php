<?php

namespace App\Commands;

use App\Guild;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Config;
use Ramsey\Uuid\Guid\Guid;

class Prefix extends CommandHandler implements CommandInterface
{
    protected $customPrefix;
    protected $prefix;

    public function execute()
    {
        if(!is_null($this->player))
        {
            return 'Coming soon';


            echo PHP_EOL.'Execute Prefix'.PHP_EOL;
            if($this->player->ban)
                return trans('generic.banned',[],$this->player->lang);

            if(!is_null($this->player->vacation))
                return trans('profile.vacationMode',[],$this->player->lang);

            if(is_null($this->message->channel->guild_id))
                return trans('prefix.noCustomPrefixInDM', [], $this->player->lang);

            $this->prefix = $this->discord->commandClientOptions['prefix'];
            if(!is_null($this->message->channel->guild_id))
            {
                $guildConfig = config('stargate.guilds.'.$this->message->channel->guild_id);
                if(!is_null($guildConfig))
                {
                    $this->prefix = $guildConfig['prefix'];
                    $this->customPrefix = true;
                }
            }

            $perms = $this->message->author->getPermissions();
            if(!$perms->administrator)
                return trans('generic.adminrestricted', [], $this->player->lang);


            if(empty($this->args))
            {
                return trans('prefix.actualPrefix', ['prefix' => $this->prefix], $this->player->lang);
            }
            else
            {
                $newPrefix = trim($this->args[0]);
                if( empty($newPrefix) || strlen($newPrefix) > 3 )
                    return trans('prefix.wrongPrefixSize', [], $this->player->lang);


                try{
                    $guild = Guild::Where('guild_id', $this->message->channel->guild_id)->first();
                    if(!is_null($guild))
                    {
                        $guild->guild_name = $this->message->channel->guild->name;
                        $guild->prefix = $newPrefix;
                        Config::set('stargate.guilds.'.$this->message->channel->guild_id.'.prefix', $newPrefix);
                    }
                    else
                    {
                        $guild = new Guild;
                        $guild->guild_id = $this->message->channel->guild_id;
                        $guild->guild_name = $this->message->channel->guild->name;
                        $guild->prefix = $newPrefix;
                        Config::set('stargate.guilds.'.$this->message->channel->guild_id.'.prefix', $newPrefix);
                    }
                    $guild->save();
                    $this->message->reply(trans('prefix.newPrefixSetted', ['prefix' => $newPrefix], $this->player->lang));
                }
                catch(\Exception $e)
                {
                    echo PHP_EOL.'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                }

            }
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
    }
}
