<?php

namespace App\Commands;

use App\Guild;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Discord\Parts\Embed\Embed;
use Illuminate\Support\Facades\Config;
use Ramsey\Uuid\Guid\Guid;

class Tutorial extends CommandHandler implements CommandInterface
{
    protected $customPrefix;
    protected $prefix;

    public function execute()
    {
        if(!is_null($this->player))
        {
            echo PHP_EOL.'Execute Tutorial'.PHP_EOL;
            if($this->player->ban)
                return trans('generic.banned',[],$this->player->lang);

            if(!is_null($this->player->vacation))
                return trans('profile.vacationMode',[],$this->player->lang);

            $embed = [
                'author' => [
                    'name' => $this->player->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                ],
                //'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/malpSending.gif'],
                "title" => trans('tutorial.welcome',[],$this->player->lang),
                "description" => trans('tutorial.tutorialContent',[],$this->player->lang),
                'fields' => [
                ],
                'footer' => array(
                    'text'  => 'Stargate',
                ),
            ];
            $newEmbed = $this->discord->factory(Embed::class,$embed);
            $this->message->channel->sendMessage('', false, $newEmbed);
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
    }
}
