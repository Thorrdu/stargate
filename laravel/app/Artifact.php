<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Colony;
use Carbon\Carbon;
use Carbon\CarbonInterface;


class Artifact extends Model
{
    public function colony(){
        return $this->belongsTo('App\Colony');
    }

    public function toString($lang = 'en')
    {
        $artifactString = '';
        if($this->bonus_category == "Price")
        {
            switch($this->bonus_type)
            {
                case 'Research':
                    $bonusName = config('stargate.emotes.research')." ".trans('generic.researchPrice', [], $lang);
                break;
                case 'Building':
                    $bonusName = config('stargate.emotes.productionBuilding')." ".trans('generic.buildingPrice', [], $lang);
                break;
                case 'Ship':
                    $bonusName = trans('generic.shipPrice', [], $lang);
                break;
                case 'Defence':
                    $bonusName = trans('generic.defencePrice', [], $lang);
                break;
                case 'Craft':
                    $bonusName = config('stargate.emotes.productionBuilding')." ".trans('generic.craftingPrice', [], $lang);
                break;
            }

            if($this->bonus_coef < 1)
                $artifactString .= "$bonusName: -".((1-$this->bonus_coef)*100).'%';
            else
                $artifactString .= "$bonusName: +".(($this->bonus_coef-1)*100).'%';
        }
        elseif($this->bonus_category == "Time")
        {
            switch($this->bonus_type)
            {
                case 'Research':
                    $bonusName = config('stargate.emotes.research')." ".trans('generic.researchTime', [], $lang)."\n";
                break;
                case 'Building':
                    $bonusName = config('stargate.emotes.productionBuilding')." ".trans('generic.buildingTime', [], $lang);
                break;
                case 'Ship':
                    $bonusName = trans('generic.shipTime', [], $lang);
                break;
                case 'Defence':
                    $bonusName = trans('generic.defenceTime', [], $lang);
                break;
                case 'Craft':
                    $bonusName = config('stargate.emotes.productionBuilding')." ".trans('generic.craftingTime', [], $lang);
                break;
            }

            if($this->bonus_coef < 1)
                $artifactString .= "$bonusName: -".((1-$this->bonus_coef)*100).'%';
            else
                $artifactString .= "$bonusName: +".(($this->bonus_coef-1)*100).'%';
        }
        elseif($this->bonus_category == "Production")
        {
            $resName = ucfirst($this->bonus_resource);
            if(in_array($this->bonus_resource,array('e2pz','militaries')))
                $resName = trans('generic.'.strtolower($this->bonus_resource), [], $lang);
            if($this->bonus_resource == 'military')
                $resName = trans('generic.militaries', [], $lang);

            if($this->bonus_coef < 1)
                $artifactString .= config('stargate.emotes.'.strtolower($this->bonus_resource))." $resName: -".((1-$this->bonus_coef)*100).'%';
            else
                $artifactString .= config('stargate.emotes.'.strtolower($this->bonus_resource))." $resName: +".(($this->bonus_coef-1)*100).'%';
        }
        elseif($this->bonus_category == "ColonyMax")
            $artifactString .= trans('colony.colonyMaxBonus', [], $lang).": +".number_format($this->bonus_coef);
        elseif($this->bonus_category == "DefenceLure")
        {
            if($this->bonus_coef == 2)
                $artifactString .= trans('colony.defenceDoubled', [], $lang);
            else
                $artifactString .= trans('colony.defenceDivided', [], $lang);
        }
        else
        {
            if($this->bonus_coef < 1)
                $artifactString .= "$this->bonus_category: -".((1-$this->bonus_coef)*100).'%';
            else
                $artifactString .= "$this->bonus_category: +".(($this->bonus_coef-1)*100).'%';
        }

        if(!is_null($this->bonus_end))
        {
            $now = Carbon::now();
            $buildingTime = $now->diffForHumans($this->bonus_end,[
                'parts' => 3,
                'short' => true, // short syntax as per current locale
                'syntax' => CarbonInterface::DIFF_ABSOLUTE
            ]);   

            $artifactString .= ' ('.$buildingTime.')';

        }
        else
            $artifactString .= ' ('.trans('generic.permanent', [], $lang).')';

        return $artifactString;
    }
}
