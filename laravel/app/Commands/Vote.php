<?php

namespace App\Commands;

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

                    if($this->player->activeColony->artifacts->count() < 10)
                    {
                        $this->player->vote_boxes--;
                        $this->player->save();
                        $newArtifact = $this->player->activeColony->generateArtifact(['forceBonus' => true,'minEnding' => 6, 'maxEnding' => 6])->toString($this->player->lang);
                        return trans('vote.voteBoxOpening', ['artifact' => $newArtifact], $this->player->lang);
                    }
                    else
                        return trans('vote.tooManyArtifacts', [], $this->player->lang);

                }
                catch(\Exception $e)
                {
                    echo $e->getMessage();
                    return $e->getMessage();
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
