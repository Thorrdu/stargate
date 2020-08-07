<?php

namespace App\Commands;

use App\Unit;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

class Craft extends CommandHandler implements CommandInterface
{
    public $page;
    public $maxPage;
    public $maxTime;
    public $paginatorMessage;
    public $listner;
    public $craftList;
    
    public function execute()
    {
        if(!is_null($this->player))
        {
            if($this->player->ban)
                return trans('generic.banned',[],$this->player->lang);

            try{
                if(empty($this->args) || $this->args[0] == 'list')
                {
                    echo PHP_EOL.'Execute Craft';
                    $this->craftList = Unit::all();      
                    
                    $this->page = 1;
                    $this->maxPage = ceil($this->craftList->count()/5);
                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage('', false, $this->getPage())->then(function ($messageSent){
                        $this->paginatorMessage = $messageSent;
                        $this->paginatorMessage->react('⏪')->then(function(){ 
                            $this->paginatorMessage->react('◀️')->then(function(){ 
                                $this->paginatorMessage->react('▶️')->then(function(){ 
                                    $this->paginatorMessage->react('⏩');
                                });
                            });
                        });
    
                        $this->listner = function ($messageReaction) {
                            if($this->maxTime < time())
                                $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
    
                            if($messageReaction->message_id == $this->paginatorMessage->id && $messageReaction->user_id == $this->player->user_id)
                            {
                                if($messageReaction->emoji->name == '⏪')
                                {
                                    $this->page = 1;
                                    $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getPage());
                                    $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                                }
                                elseif($messageReaction->emoji->name == '◀️' && $this->page > 1)
                                {
                                    $this->page--;
                                    $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getPage());
                                    $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                                }
                                elseif($messageReaction->emoji->name == '▶️' && $this->maxPage > $this->page)
                                {
                                    $this->page++;
                                    $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getPage());
                                    $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                                }
                                elseif($messageReaction->emoji->name == '⏩')
                                {
                                    $this->page = $this->maxPage;
                                    $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getPage());
                                    $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                                }
                            }
                        };
                        $this->discord->on('MESSAGE_REACTION_ADD', $this->listner);
                    });
                }
                elseif($this->args[0] == "queue")
                {
                    return 'display queue';
                }
                else
                {
                    $unit = Unit::where('id', (int)$this->args[0])->orWhere('slug', 'LIKE', $this->args[0].'%')->first();
                    if(!is_null($unit))
                    {
                        $qty = 1;
                        if(count($this->args) == 1 && is_int($this->args[1]) && $this->args[1] > 0)
                            $qty = $this->args[1];

                        //Requirement
                        $hasRequirements = true;
                        foreach($unit->requiredTechnologies as $requiredTechnology)
                        {
                            $currentLvl = $this->player->hasTechnology($requiredTechnology);
                            if(!($currentLvl && $currentLvl >= $requiredTechnology->pivot->level))
                                $hasRequirements = false;
                        }
                        foreach($unit->requiredBuildings as $requiredBuilding)
                        {
                            $currentLvl = $this->player->colonies[0]->hasBuilding($requiredBuilding);
                            if(!($currentLvl && $currentLvl >= $requiredBuilding->pivot->level))
                                $hasRequirements = false;
                        }
                        if(!$hasRequirements)
                            return trans('generic.missingRequirements', [], $this->player->lang);
                        
                        $hasEnough = true;
                        $unitPrices = $unit->getPrice($qty);

                        $missingResString = "";
                        foreach (config('stargate.resources') as $resource)
                        {
                            if($unit->$resource > 0 && $unitPrices[$resource] > $this->player->colonies[0]->$resource)
                            {
                                $hasEnough = false;
                                $missingResString .= " ".config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format($unitPrices[$resource]-$this->player->colonies[0]->$resource);
                            }
                        }
                        if(!$hasEnough)
                            return trans('generic.notEnoughResources', ['missingResources' => $missingResString], $this->player->lang);

                        if(!is_null($this->player->active_technology_id) && $unit->id == 15)
                            return trans('generic.busyBuilding', [], $this->player->lang);

                        $now = Carbon::now();
                        $endingDate = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->colonies[0]->startCrafting($unit,$qty));
                        $buildingTime = $now->diffForHumans($endingDate,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);
                        return trans('craft.buildingStarted', ['name' => $unit->name, 'qty' => $qty, 'time' => $buildingTime], $this->player->lang);
                    
                    }
                    else
                        return trans('craft.unknownCraft', [], $this->player->lang);
                }
            }
            catch(\Exception $e)
            {
                echo $e->getMessage();
                return $e->getMessage();
            }
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
        return false;
    }

    public function getPage()
    {
        $displayList = $this->craftList->skip(5*($this->page -1))->take(5);
        
        $embed = [
            'author' => [
                'name' => $this->player->user_name,
                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
            ],
            "title" => trans('craft.craftList', [], $this->player->lang),
            "description" => trans('craft.genericHowTo', [], $this->player->lang),
            'fields' => [],
            'footer' => array(
                //'icon_url'  => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png',
                'text'  => 'Stargate - '.trans('generic.page', [], $this->player->lang).' '.$this->page.' / '.$this->maxPage,
            ),
        ];

        foreach($displayList as $unit)
        {
            $unitPrice = "";
            $unitPrices = $unit->getPrice();
            foreach (config('stargate.resources') as $resource)
            {
                if($unit->$resource > 0)
                {
                    if(!empty($unitPrice))
                        $unitPrice .= " ";
                    $unitPrice .= config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(round($unitPrices[$resource]));
                }
            }
            $unitTime = $unit->base_time;

            /** Application des bonus */
            $unitTime *= $this->player->colonies[0]->getCraftingBonus();

            $now = Carbon::now();
            $unitEnd = $now->copy()->addSeconds($unitTime);
            $unitTime = $now->diffForHumans($unitEnd,[
                'parts' => 3,
                'short' => true, // short syntax as per current locale
                'syntax' => CarbonInterface::DIFF_ABSOLUTE
            ]);      

            $hasRequirements = true;
            foreach($unit->requiredTechnologies as $requiredTechnology)
            {
                $currentLvlOwned = $this->player->hasTechnology($requiredTechnology);
                if(!($currentLvlOwned && $currentLvlOwned >= $requiredTechnology->pivot->level))
                    $hasRequirements = false;
            }
            foreach($unit->requiredBuildings as $requiredBuilding)
            {
                $currentLvlOwned = $this->player->colonies[0]->hasBuilding($requiredBuilding);
                if(!($currentLvlOwned && $currentLvlOwned >= $requiredBuilding->pivot->level))
                    $hasRequirements = false;
            }

            if($hasRequirements == true)
            {
                $embed['fields'][] = array(
                    'name' => $unit->id.' - '.$unit->name,
                    'value' => "\nSlug: `".$unit->slug."`\n - ".trans('generic.duration', [], $this->player->lang).": ".$unitTime."\n".trans('generic.price', [], $this->player->lang).": ".$unitPrice,
                    'inline' => true
                );
            }
            else
            {
                $embed['fields'][] = array(
                    'name' => trans('craft.hidden', [], $this->player->lang),
                    'value' => trans('craft.unDiscovered', [], $this->player->lang),
                    'inline' => true
                );
            }
        }

        return $embed;
    }

}