<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\DiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use App\Player;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class Colony extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {

            try{
                echo PHP_EOL.'Execute Colony';
                if($this->player->ban)
                    return trans('generic.banned',[],$this->player->lang);
                $this->player->colonies[0]->checkColony();
                $this->player->refresh();

                $coordinates = $this->player->colonies[0]->coordinates;

                $embed = [
                    'author' => [
                        'name' => $this->player->user_name,
                        'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                    ],
                    "title" => 'Colonie '.$this->player->colonies[0]->name,
                    "description" => trans('generic.coordinates', [], $this->player->lang).": ".$coordinates->galaxy.":".$coordinates->system.":".$coordinates->planet,
                    'fields' => [],
                    'footer' => array(
                        'text'  => 'Stargate',
                    ),
                ];

                $resourcesValue = "";
                $productionValue = '';
                $storageValue = "";
                foreach (config('stargate.resources') as $resource)
                {
                    if(!empty($resourcesValue))
                    {
                        $resourcesValue .= "\n";
                        $productionValue .= "\n";
                    }
                    $resourcesValue .= config('stargate.emotes.'.$resource).' '.ucfirst($resource).": ".number_format($this->player->colonies[0]->$resource)." (".number_format($this->player->colonies[0]['production_'.$resource])."/h)";
                    $storageValue .= number_format($this->player->colonies[0]['storage_'.$resource]).' '.ucfirst($resource)."\n";
                }

                if(!empty($resourcesValue))
                {
                    $resourcesValue .= "\n".config('stargate.emotes.energy')." ".trans('generic.energy', [], $this->player->lang).": ".($this->player->colonies[0]->energy_max - round($this->player->colonies[0]->energy_used)).' / '.$this->player->colonies[0]->energy_max;
                    $resourcesValue .= "\n".config('stargate.emotes.military')." ".trans('generic.militaries', [], $this->player->lang).": ".number_format($this->player->colonies[0]->military)." (".$this->player->colonies[0]->production_military."/h)";
                    $resourcesValue .= "\n".config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).": ".number_format($this->player->colonies[0]->E2PZ)." (".$this->player->colonies[0]->production_e2pz."/w)";
                    $embed['fields'][] = array(
                                            'name' => config('stargate.emotes.production')." ".trans('generic.resources', [], $this->player->lang),
                                            'value' => $resourcesValue,
                                            'inline' => true
                                        );
                }


                $prodBuildings = $this->player->colonies[0]->buildings->filter(function ($value) {
                    return $value->type == 'Production' || $value->type == "Energy";
                });
                $prodBuildingsValue = "";
                foreach($prodBuildings as $prodBuilding)
                {
                    if(!empty($prodBuildingsValue))
                        $prodBuildingsValue .= "\n";
                    $prodBuildingsValue .= 'Lvl '.$prodBuilding->pivot->level.' - '.$prodBuilding->name;
                }
                if(!empty($prodBuildingsValue))
                {
                    $embed['fields'][] = array(
                                            'name' => config('stargate.emotes.productionBuilding')." ".trans('generic.productionBuildings', [], $this->player->lang),
                                            'value' => $prodBuildingsValue,
                                            'inline' => true
                                        );
                }

                $scienceBuildings = $this->player->colonies[0]->buildings->filter(function ($value) {
                    return $value->type == "Science";
                });
                $scienceBuildingsValue = "";
                foreach($scienceBuildings as $scienceBuilding)
                {
                    if(!empty($scienceBuildingsValue))
                        $scienceBuildingsValue .= "\n";
                    $scienceBuildingsValue .= 'Lvl '.$scienceBuilding->pivot->level.' - '.$scienceBuilding->name;
                }
                if(!empty($scienceBuildingsValue))
                {
                    $embed['fields'][] = array(
                                            'name' => config('stargate.emotes.research')." ".trans('generic.scienceBuildings', [], $this->player->lang),
                                            'value' => $scienceBuildingsValue,
                                            'inline' => true
                                        );
                }

                $militaryBuildings = $this->player->colonies[0]->buildings->filter(function ($value) {
                    return $value->type == "Military";
                });
                $militaryBuildingsValue = "";
                foreach($militaryBuildings as $militaryBuilding)
                {
                    if(!empty($militaryBuildingsValue))
                        $militaryBuildingsValue .= "\n";
                    $militaryBuildingsValue .= 'Lvl '.$militaryBuilding->pivot->level.' - '.$militaryBuilding->name;
                }
                if(!empty($militaryBuildingsValue))
                {
                    $embed['fields'][] = array(
                                            'name' => config('stargate.emotes.military')." ".trans('generic.militaryBuildings', [], $this->player->lang),
                                            'value' => $militaryBuildingsValue,
                                            'inline' => true
                                        );
                }

                if(!empty($storageValue))
                {
                    $storageValue = "\n".trans('generic.buildingSpace', [], $this->player->lang).": ".($this->player->colonies[0]->space_max - $this->player->colonies[0]->space_used).' / '.$this->player->colonies[0]->space_max."\n".$storageValue;

                    $embed['fields'][] = array(
                                            'name' => config('stargate.emotes.storage')." ".trans('generic.storageCapacity', [], $this->player->lang),
                                            'value' => $storageValue,
                                            'inline' => true
                                        );
                }

                $technologyValue = "";
                foreach($this->player->technologies as $technology)
                {
                    if(!empty($technologyValue))
                        $technologyValue .= "\n";
                    $technologyValue .= 'Lvl '.$technology->pivot->level.' - '.$technology->name;
                }
                if(!empty($technologyValue))
                {
                    $embed['fields'][] = array(
                                            'name' => config('stargate.emotes.research')." ".trans('generic.technologies', [], $this->player->lang),
                                            'value' => $technologyValue,
                                            'inline' => true
                                        );
                }

                if(count($this->player->colonies[0]->units) > 0)
                {
                    $unitsString = '';
                    foreach($this->player->colonies[0]->units as $unit)
                    {
                        $unitsString .= number_format($unit->pivot->number).' '.$unit->name."\n";
                    }
                    $embed['fields'][] = array(
                                            'name' => trans('generic.units', [], $this->player->lang),
                                            'value' => $unitsString,
                                            'inline' => true
                                        );
                }

                $now = Carbon::now();
                if(!is_null($this->player->colonies[0]->active_building_end)){
                    $buildingEnd = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->colonies[0]->active_building_end);
                    $buildingTime = $now->diffForHumans($buildingEnd,[
                        'parts' => 3,
                        'short' => true, // short syntax as per current locale
                        'syntax' => CarbonInterface::DIFF_ABSOLUTE
                    ]);

                    $currentLevel = $this->player->colonies[0]->hasBuilding($this->player->colonies[0]->activeBuilding);
                    if(!$currentLevel)
                        $currentLevel = 0;
                    $embed['fields'][] = array(
                        'name' => trans('colony.buildingUnderConstruction', [], $this->player->lang),
                        'value' => "Lvl ".($currentLevel+1)." - ".$this->player->colonies[0]->activeBuilding->name."\n".$buildingTime,
                        'inline' => true
                    );
                }

                if(!is_null($this->player->active_technology_end)){
                    $buildingEnd = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->active_technology_end);
                    $buildingTime = $now->diffForHumans($buildingEnd,[
                        'parts' => 3,
                        'short' => true, // short syntax as per current locale
                        'syntax' => CarbonInterface::DIFF_ABSOLUTE
                    ]);

                    $currentLevel = $this->player->hasTechnology($this->player->activeTechnology);
                    if(!$currentLevel)
                        $currentLevel = 0;
                    $embed['fields'][] = array(
                        'name' => trans('colony.technologyUnderResearch', [], $this->player->lang),
                        'value' => "Lvl ".($currentLevel+1)." - ".$this->player->activeTechnology->name."\n".$buildingTime,
                        'inline' => true
                    );
                }

                if($this->player->colonies[0]->craftQueues->count() > 0){
                    $queueString = "";
                    $queuedUnits = $this->player->colonies[0]->craftQueues()->limit(5)->get();
                    foreach($queuedUnits as $queuedUnit)
                    {
                        $buildingEnd = Carbon::createFromFormat("Y-m-d H:i:s",$queuedUnit->pivot->craft_end);
                        $buildingTime = $now->diffForHumans($buildingEnd,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);
                        $queueString .= $queuedUnit->name." - ".$buildingTime."\n";    
                    }
                    if($this->player->colonies[0]->craftQueues->count() > 5)
                    {
                        $lastQueue = $this->player->colonies[0]->craftQueues()->where('craft_end','>',Carbon::now())->orderBy('craft_end', 'DESC')->first();
                        $buildingEnd = Carbon::createFromFormat("Y-m-d H:i:s",$lastQueue->pivot->craft_end);
                        $buildingTime = $now->diffForHumans($buildingEnd,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);
                        $queueString .= "... - ".$buildingTime."\n";
                    }

                    $embed['fields'][] = array(
                        'name' => trans('colony.craftQueue', [], $this->player->lang),
                        'value' => $queueString,
                        'inline' => true
                    );
                }

                $this->message->channel->sendMessage('', false, $embed);

            }
            catch(\Exception $e)
            {
                return $e->getMessage();
            }
        }       
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
        return false;
    }
}
