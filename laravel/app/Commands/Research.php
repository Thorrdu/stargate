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
            echo PHP_EOL.'Execute Refresh';

            if(empty($this->args) || $this->args[0] == 'list')
            {
                echo PHP_EOL.'Execute Search';
                $embed = [
                    'author' => [
                        'name' => $this->player->user_name,
                        'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                    ],
                    "title" => 'Liste des technologies',
                    "description" => 'Pour commencer la recherche d\'une technologie utilisez `!build [Numéro]`',
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
    
                $this->message->channel->sendMessage('Technology Embed', false, $embed);
            }
            else
            {
                $technologyId = (int)$this->args[0];
                $technology = Building::find($technologyId);
                if(!is_null($technology))
                {
                    //if recherche en cours, return
                    if(!is_null($this->player->active_technology_end))
                        return 'Une technologie est déjà en cours de recherche';

                    $wantedLvl = 1;
                    $currentLevel = $this->player->hadTechnology($technology);
                    if($currentLevel)
                        $wantedLvl += $currentLevel;

                    $hasEnough = true;
                    $technologyPrices = $technology->getPrice($wantedLvl);
                    foreach (config('stargate.resources') as $resource)
                    {
                        if($technology->$resource > 0 && $technologyPrices[$resource] > $this->player->colonies[0]->$resource)
                            $hasEnough = false;
                    }

                    if($hasEnough)
                    {
                        $endingDate = $this->player->colonies[0]->startBuilding($technology);
                        return 'Recherche commencée, **'.$technology->name.' LVL '.$wantedLvl.'** sera terminé le: '.$endingDate;
                    }
                    else
                    {
                        return 'Vous ne possédez pas assez de ressource pour rechercher cette technologie.';
                    }
                }
            }
        }
        return false;
    }
}
