<?php

namespace App\Commands;

use App\Channel;
use App\Guild;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Ramsey\Uuid\Guid\Guid;

class ChannelCommand extends CommandHandler implements CommandInterface
{

    public function execute()
    {
        if(!is_null($this->player))
        {
            return 'Coming soon';

            echo PHP_EOL.'Execute Channel'.PHP_EOL;
            if($this->player->ban)
                return trans('generic.banned',[],$this->player->lang);

            if(!is_null($this->player->vacation))
                return trans('profile.vacationMode',[],$this->player->lang);

            if(is_null($this->message->channel->guild_id))
                return trans('generic.noCustomChannelInDM', [], $this->player->lang);

            if(count($this->args) < 2)
            {
                return trans('generic.wrongParameter', [], $this->player->lang);
            }

            $perms = $this->message->author->getPermissions();
            if(!$perms->administrator)
                return trans('generic.adminrestricted', [], $this->player->lang);

            elseif(Str::startsWith($this->args[0], 'ignore'))
            {
                try{
                    $channel = Channel::Where('channel_id', $this->message->channel->id)->first();
                    if(is_null($channel))
                    {
                        $channel = new Channel;
                        $channel->guild_id = $this->message->channel->guild_id;
                        $channel->channel_id = $this->message->channel->id;
                        $channel->channel_name = $this->message->channel->name;
                    }

                    if(Str::startsWith($this->args[1], 'on'))
                    {
                        $channel->ignore = true;
                        Config::set('stargate.channels.'.$this->message->channel->id.'.ignore', 'on');

                    }
                    elseif(Str::startsWith($this->args[1], 'off'))
                    {
                        $channel->ignore = false;
                        Config::set('stargate.channels.'.$this->message->channel->id.'.ignore', 'off');
                    }
                    else
                        return trans('generic.wrongParameter', [], $this->player->lang);

                    $channel->save();
                    if($channel->ignore)
                        $this->message->reply(trans('generic.channelIgnored', [], $this->player->lang));
                    else
                        $this->message->reply(trans('generic.channelNotIgnored', [], $this->player->lang));

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
