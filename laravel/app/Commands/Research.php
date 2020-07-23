<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\DiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use App\Player;
use App\Building;
use App\Technology;
use Carbon\Carbon;

class Research extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            echo PHP_EOL.'Execute Research';

            if(empty($this->args) || $this->args[0] == 'list')
            {
                $embed = [
                    'author' => [
                        'name' => $this->player->user_name,
                        'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                    ],
                    "title" => 'Liste des technologies',
                    "description" => 'Pour commencer la recherche d\'une technologie utilisez `!research [Numéro]`',
                    'fields' => [],
                    'footer' => array(
                        'text'  => 'Stargate',
                    ),
                ];

                $technologies = Technology::all();
                foreach($technologies as $technology)
                {
                    $wantedLevel = 1;
                    $currentLvl = $this->player->hasTechnology($technology);
                    if($currentLvl)
                        $wantedLevel += $currentLvl;

                    $buildingPrice = "";
                    $buildingPrices = $technology->getPrice($wantedLevel);
                    foreach (config('stargate.resources') as $resource)
                    {
                        if($technology->$resource > 0)
                        {
                            if(!empty($buildingPrice))
                                $buildingPrice .= " ";
                            $buildingPrice .= number_format(round($buildingPrices[$resource])).' '.ucfirst($resource);
                        }
                    }

                    $buildingTime = $technology->getTime($wantedLevel);

                    /** Application des bonus */
                    $buildingTime *= $this->player->getResearchBonus();

                    $buildingTime = gmdate("H:i:s", $buildingTime);

                    $displayedLvl = 0;
                    if($currentLvl)
                        $displayedLvl = $currentLvl;
                        
                    $embed['fields'][] = array(
                        'name' => $technology->id.' - '.$technology->name.' - LVL '.$displayedLvl,
                        'value' => 'Description: '.$technology->description."\nTemps: ".$buildingTime."\nCondition: /\nPrix: ".$buildingPrice
                    );
                }
    
                $this->message->channel->sendMessage('', false, $embed);
            }
            else
            {
                $technologyId = (int)$this->args[0];
                $technology = Technology::find($technologyId);
                if(!is_null($technology))
                {
                    //if recherche en cours, return
                    if(!is_null($this->player->active_technology_end))
                        return 'Une technologie est déjà en cours de recherche';

                    $wantedLvl = 1;
                    $currentLevel = $this->player->hasTechnology($technology);
                    if($currentLevel)
                        $wantedLvl += $currentLevel;

                    $hasEnough = true;
                    $technologyPrices = $technology->getPrice($wantedLvl);
                    foreach (config('stargate.resources') as $resource)
                    {
                        if($technology->$resource > 0 && $technologyPrices[$resource] > $this->player->colonies[0]->$resource)
                            $hasEnough = false;
                    }

                    if(!$hasEnough)
                        return 'Vous ne possédez pas assez de ressource pour rechercher cette technologie.';

                    $endingDate = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->startResearch($technology))->timestamp;
                    $buildingTime = gmdate("H:i:s", $endingDate - time());

                    return 'Recherche commencée, **'.$technology->name.' LVL '.$wantedLvl.'** sera terminé dans '.$buildingTime;

                }
            }
        }
        else
            return "Pour commencer votre aventure, utilisez `!start`";
        return false;
    }
}
