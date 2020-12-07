<?php

namespace App\Commands;

use App\Utility\PlayerUtility;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

class Vote extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player) && $this->player->ban)
            return trans('generic.banned',[],$this->player->lang);

        echo PHP_EOL.'Execute VOTE';
        if(!is_null($this->player) && !empty($this->args) && Str::startsWith('use', $this->args[0]))
        {
            if(!is_null($this->player->vacation))
                return trans('profile.vacationMode', [], $this->player->lang);

            if($this->player->vote_boxes > 0)
            {
                try{
                    if($this->player->activeColony->artifacts->count() >= 10)
                        return trans('vote.tooManyArtifacts', [], $this->player->lang);

                    $bonusTypes = ['Artifact', 'Premium'];
                    $bonusWeights = [
                        'Artifact' => 99,
                        'Premium' => 1
                    ];
                    $bonusType = PlayerUtility::rngWeighted($bonusTypes,$bonusWeights);

                    switch($bonusType)
                    {
                        case 'Premium':
                            $this->player->vote_boxes--;

                            if(!is_null($this->player->premium_expiration))
                            {
                                $this->player->premium_expiration = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->premium_expiration)->add('24h');
                                $this->player->save();
                            }
                            else
                            {
                                $this->player->premium_expiration = Carbon::now()->add('24h');
                                $this->player->save();

                                foreach($this->player->colonies as $colony)
                                {
                                    $colony->calcProd(); //reload Prods
                                    $colony->save();
                                }
                            }

                            $newArtifact = trans('vote.premiumWin',[], $this->player->lang);
                            return trans('vote.voteBoxOpening', ['artifact' => $newArtifact], $this->player->lang);
                        break;
                        case 'Artifact':
                            $this->player->vote_boxes--;
                            $this->player->save();
                            $newArtifact = $this->player->activeColony->generateArtifact(['forceBonus' => true,'minEnding' => 12, 'maxEnding' => 12])->toString($this->player->lang);
                            return trans('vote.voteBoxOpening', ['artifact' => $newArtifact], $this->player->lang);
                        default:

                        break;
                    }



                }
                catch(\Exception $e)
                {
                    echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                    return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                }
            }
            else
                return trans('generic.notEnoughResources', ['missingResources' => '1 Vote Box'], $this->player->lang);
        }
        else
        {
            $now = Carbon::now();
            if(!is_null($this->player->vote_available))
            {
                $availableDate = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->vote_available);
                if($availableDate > $now)
                {
                    $nextVote = $now->diffForHumans($availableDate,[
                        'parts' => 3,
                        'short' => true, // short syntax as per current locale
                        'syntax' => CarbonInterface::DIFF_ABSOLUTE
                    ]);
                    return trans('vote.voteTimer', ['time' => $nextVote,'voteBoxes' => $this->player->vote_boxes], $this->player->lang);
                }
            }

            if(!is_null($this->player))
                return trans('vote.voteMessage',['link'=>'https://top.gg/bot/730815388400615455/vote','voteBoxes' => $this->player->vote_boxes], $this->player->lang);
            else
                return trans('vote.voteMessage',['link'=>'https://top.gg/bot/730815388400615455/vote','voteBoxes' => 0]);
        }


    }
}
