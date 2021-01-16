<?php

namespace App\Commands;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use App\Player;
use App\Alliance;
use App\AllianceRole;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use \Discord\Parts\Channel\Message as Message;
use Discord\Parts\Embed\Embed;

class AllianceCommand extends CommandHandler implements CommandInterface
{
    public $page;
    public $maxPage;
    public $maxTime;
    public $paginatorMessage;
    public $listner;
    public $allianceList;
    public $closed;

    public function execute()
    {
        try{
            if(!is_null($this->player))
            {
                echo PHP_EOL.'Execute Alliance';
                if($this->player->ban)
                    return trans('generic.banned',[],$this->player->lang);

                if($this->player->captcha)
                    return trans('generic.captchaMessage',[],$this->player->lang);

                if(!is_null($this->player->vacation))
                    return trans('profile.vacationMode',[],$this->player->lang);

                try{
                    if(empty($this->args))
                    {
                        //Display actual alliance
                        if(is_null($this->player->alliance))
                            return trans('alliance.noAlliance',[],$this->player->lang);
                        else
                        {
                            $alliance = $this->player->alliance;
                            if($alliance->recruitement_status == 1)
                                $recrutementStatusString = trans('generic.yes', [], $this->player->lang);
                            else
                                $recrutementStatusString = trans('generic.no', [], $this->player->lang);

                            $totalAlliances = DB::table('alliances')->count();
                            $generalPosition = DB::table('alliances')->where([['id', '!=', 1],['points_total', '>' , $alliance->points_total]])->count() + 1;
                            $buildingPosition = DB::table('alliances')->where([['id', '!=', 1],['points_building', '>' , $alliance->points_building]])->count() + 1;
                            $researchPosition = DB::table('alliances')->where([['id', '!=', 1],['points_research', '>' , $alliance->points_research]])->count() + 1;
                            $craftPosition = DB::table('alliances')->where([['id', '!=', 1],['points_craft', '>' , $alliance->points_craft]])->count() + 1;
                            $militaryPosition = DB::table('alliances')->where([['id', '!=', 1],['points_military', '>' , $alliance->points_military]])->count() + 1;
                            //$defencePosition = DB::table('alliances')->where([['id', '!=', 1],['points_defence', '>' , $alliance->points_defence]])->count() + 1;

                            $topPosition = trans('generic.general',[],$this->player->lang).": ".number_format($alliance->points_total)." Points (Position: ".number_format($generalPosition)."/{$totalAlliances})\n"
                            .config('stargate.emotes.productionBuilding')." ".trans('generic.building',[],$this->player->lang).": Points ".number_format($alliance->points_building)." (".number_format($buildingPosition)."/{$totalAlliances})\n"
                            .config('stargate.emotes.research')." ".trans('generic.research',[],$this->player->lang).": Points ".number_format($alliance->points_research)." (Position: ".number_format($researchPosition)."/{$totalAlliances})\n"
                            .config('stargate.emotes.craft')." ".trans('generic.unit',[],$this->player->lang).": Points ".number_format($alliance->points_craft)." (Position: ".number_format($craftPosition)."/{$totalAlliances})\n"
                            .config('stargate.emotes.military')." ".trans('generic.military',[],$this->player->lang).": Points ".number_format($alliance->points_military)." (Position: ".number_format($militaryPosition)."/{$totalAlliances})\n";
                            //.config('stargate.emotes.defence')." ".trans('generic.defence',[],$this->player->lang).": Points ".number_format($alliance->points_defence)." (Position: ".number_format($defencePosition)."/{$totalAlliances})\n";

                            //Display Current Alliance
                            $embed = [
                                'author' => [
                                    'name' => $this->player->user_name,
                                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                                ],
                                "title" => trans('alliance.alliance', [], $this->player->lang),
                                "description" => trans('alliance.generalDescription', [
                                    'allianceName' => $alliance->name,
                                    'allianceTag' => $alliance->tag,
                                    'founder' => $alliance->founder->untagged_user_name,
                                    'leader' => $alliance->leader->untagged_user_name,
                                    'memberCount' => $alliance->members->count()."/".$alliance->player_limit,
                                    'recruitementStatus' => $recrutementStatusString,
                                    'internalDescription' => $alliance->internal_description,
                                    'top' => $topPosition
                                ], $this->player->lang),
                                'fields' => [],
                                'footer' => array(
                                    'text'  => 'Stargate',
                                ),
                            ];

                            $membersString = "";
                            foreach($alliance->members as $member)
                            {
                                $membersString .= $member->allianceRole->name.' - '.$member->untagged_user_name."\n";
                            }
                            $embed['fields'][] = array(
                                'name' => trans('alliance.membersList', [], $this->player->lang),
                                'value' => $membersString,
                                'inline' => false
                            );
                            $newEmbed = $this->discord->factory(Embed::class,$embed);
                            $this->message->channel->sendMessage('', false, $newEmbed);

                        }
                    }
                    elseif(Str::startsWith('list', $this->args[0]))
                    {
                        echo PHP_EOL.'Execute alliance list';
                        $this->allianceList = Alliance::all();//with('members')->orderBy('members','desc');

                        $this->page = 1;
                        $this->maxPage = ceil($this->allianceList->count()/5);
                        $this->maxTime = time()+180;
                        $this->message->channel->sendMessage('', false, $this->getPage())->then(function ($messageSent){
                            $this->paginatorMessage = $messageSent;

                            $this->closed = false;
                            $this->paginatorMessage->react('⏪')->then(function(){
                                $this->paginatorMessage->react('◀️')->then(function(){
                                    $this->paginatorMessage->react('▶️')->then(function(){
                                        $this->paginatorMessage->react('⏩')->then(function(){
                                            $this->paginatorMessage->react(config('stargate.emotes.cancel'));
                                        });
                                    });
                                });
                            });

                            $filter = function($messageReaction){
                                if($messageReaction->user_id != $this->player->user_id || $this->closed == true)
                                    return false;

                                if($messageReaction->user_id == $this->player->user_id)
                                {
                                    try{
                                        if($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                        {
                                            $newEmbed = $this->discord->factory(Embed::class,['title' => trans('generic.closedList', [], $this->player->lang)]);
                                            $messageReaction->message->addEmbed($newEmbed);
                                            $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL, urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                                            $this->closed = true;
                                        }
                                        elseif($messageReaction->emoji->name == '⏪')
                                        {
                                            $this->page = 1;
                                            $newEmbed = $this->discord->factory(Embed::class,$this->getPage());
                                            $messageReaction->message->addEmbed($newEmbed);
                                        }
                                        elseif($messageReaction->emoji->name == '◀️' && $this->page > 1)
                                        {
                                            $this->page--;
                                            $newEmbed = $this->discord->factory(Embed::class,$this->getPage());
                                            $messageReaction->message->addEmbed($newEmbed);
                                        }
                                        elseif($messageReaction->emoji->name == '▶️' && $this->maxPage > $this->page)
                                        {
                                            $this->page++;
                                            $newEmbed = $this->discord->factory(Embed::class,$this->getPage());
                                            $messageReaction->message->addEmbed($newEmbed);
                                        }
                                        elseif($messageReaction->emoji->name == '⏩')
                                        {
                                            $this->page = $this->maxPage;
                                            $newEmbed = $this->discord->factory(Embed::class,$this->getPage());
                                            $messageReaction->message->addEmbed($newEmbed);
                                        }
                                        $messageReaction->message->deleteReaction(Message::REACT_DELETE_ID, urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                                    }
                                    catch(\Exception $e)
                                    {
                                        echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                                    }
                                    return true;
                                }
                                else
                                    return false;
                            };
                            $this->paginatorMessage->createReactionCollector($filter,['time' => config('stargate.maxCollectionTime')]);
                        });
                    }
                    else
                    {
                        if(Str::startsWith('create', $this->args[0]))
                        {
                            if(!is_null($this->player->alliance))
                                return trans('alliance.alreadyMemberOfAnAlliance',[],$this->player->lang);

                            //Alliance Creation
                            if(count($this->args) < 3)
                                return trans('generic.missingArgs',[],$this->player->lang);

                            $allianceTag = $this->args[1];
                            $allianceName = trim(join(' ', array_slice($this->args, 2)));

                            if(strlen($allianceTag) < 2)
                                return trans('alliance.tagTooShort',[],$this->player->lang);

                            if(strlen($allianceTag) > 5)
                                return trans('alliance.tagTooLong',[],$this->player->lang);

                            $allianceTagExists = Alliance::where('tag', $allianceTag)->count();
                            if($allianceTagExists > 0)
                                return trans('alliance.tagAlreadyTaken',[],$this->player->lang);

                            if(strlen($allianceName) < 2)
                                return trans('generic.nameTooShort',[],$this->player->lang);

                            $alliancNameExists = Alliance::where('name', $allianceName)->count();
                            if($alliancNameExists > 0)
                                return trans('alliance.nameAlreadyTaken',[],$this->player->lang);

                            $alliance = new Alliance;
                            $alliance->name = substr($allianceName, 0, 50);
                            $alliance->tag = substr($allianceTag, 0, 6);
                            $alliance->leader_id = $this->player->id;
                            $alliance->founder_id = $this->player->id;
                            $alliance->save();

                            $role = new AllianceRole;
                            $role->name = trans('alliance.defaultRoles.recruit', [], $this->player->lang);
                            $role->right_level = 1;
                            $role->right_recruit = false;
                            $role->right_kick = false;
                            $role->right_promote = false;
                            $role->alliance_id = $alliance->id;
                            $role->save();

                            $role = new AllianceRole;
                            $role->name = trans('alliance.defaultRoles.recruitOfficer', [], $this->player->lang);
                            $role->right_level = 2;
                            $role->right_recruit = true;
                            $role->right_kick = false;
                            $role->right_promote = false;
                            $role->alliance_id = $alliance->id;
                            $role->save();

                            $role = new AllianceRole;
                            $role->name = trans('alliance.defaultRoles.officer', [], $this->player->lang);
                            $role->right_level = 3;
                            $role->right_recruit = true;
                            $role->right_kick = true;
                            $role->right_promote = true;
                            $role->alliance_id = $alliance->id;
                            $role->save();

                            $role = new AllianceRole;
                            $role->name = trans('alliance.defaultRoles.council', [], $this->player->lang);
                            $role->right_level = 4;
                            $role->right_recruit = true;
                            $role->right_kick = true;
                            $role->right_promote = true;
                            $role->alliance_id = $alliance->id;
                            $role->save();

                            $role = new AllianceRole;
                            $role->name = trans('alliance.defaultRoles.leader', [], $this->player->lang);
                            $role->right_level = 5;
                            $role->right_recruit = true;
                            $role->right_kick = true;
                            $role->right_promote = true;
                            $role->alliance_id = $alliance->id;
                            $role->save();

                            $this->player->alliance_id = $alliance->id;
                            $this->player->role_id = $role->id;
                            $this->player->user_name = '['.$alliance->tag.'] '.$this->player->user_name;
                            $this->player->save();

                            return trans('alliance.allianceCreated', ['tag' => $alliance->tag, 'allianceName' => $alliance->name], $this->player->lang);
                        }

                        if(is_null($this->player->alliance))
                            return trans('alliance.noAlliance',[],$this->player->lang);

                        if(Str::startsWith('disband', $this->args[0]))
                        {
                            if($this->player->alliance->leader_id != $this->player->id)
                                return trans('generic.missingPermission',[],$this->player->lang);

                                $allianceId = $this->player->alliance->id;
                                $allianceName = $this->player->alliance->name;

                                $aMembers = Player::where('alliance_id', $allianceId)->get();
                                foreach($aMembers as $member){
                                    $member->user_name = $member->untagged_user_name;
                                    $member->alliance_id = null;
                                    $member->role_id = null;
                                    $member->save();
                                }

                                DB::table('alliance_roles')->where('alliance_id', $allianceId)->delete();
                                DB::table('alliances')->where('id', $allianceId)->delete();

                                return trans('alliance.allianceDisbanded', ['allianceName' => $allianceName], $this->player->lang);
                        }
                        elseif(Str::startsWith('leave', $this->args[0]))
                        {
                            if($this->player->alliance->leader_id == $this->player->id)
                                return trans('alliance.leaderCannotLeave', [], $this->player->lang);

                            $allianceName = $this->player->alliance->name;
                            $this->player->alliance_id = null;
                            $this->player->role_id = null;
                            $this->player->user_name = $this->player->untagged_user_name;
                            $this->player->save();
                            return trans('alliance.allianceLeft',['allianceName' => $allianceName], $this->player->lang);
                        }
                        elseif(Str::startsWith('upgrade', $this->args[0]))
                        {
                            $alliance = $this->player->alliance;
                            $upgradePrice = $this->getUpgradePrice($alliance->player_limit);

                            $upgradePriceString = "";
                            foreach($upgradePrice as $resource => $quantity)
                                $upgradePriceString .= config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format($quantity)."\n";

                            $upgradeMsg = trans('alliance.upgradeMessage', ['cost' => $upgradePriceString], $this->player->lang);

                            $this->maxTime = time()+180;
                            $this->message->channel->sendMessage($upgradeMsg)->then(function ($messageSent){

                                $this->closed = false;
                                $this->paginatorMessage = $messageSent;
                                $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){
                                    $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){
                                    });
                                });

                                $filter = function($messageReaction){
                                    return $messageReaction->user_id == $this->player->user_id;
                                };
                                $this->paginatorMessage->createReactionCollector($filter,['limit' => 1,'time' => config('stargate.maxCollectionTime')])->then(function ($collector){
                                    $messageReaction = $collector->first();
                                    try{
                                        if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                        {
                                            $alliance = $this->player->alliance;
                                            $alliance->refresh();
                                            $this->player->activeColony->refresh();
                                            $upgradePrice = $this->getUpgradePrice($alliance->player_limit);
                                            $hasEnough = true;
                                            $missingResString = "";
                                            foreach (config('stargate.resources') as $resource)
                                            {
                                                if($upgradePrice[$resource] > $this->player->activeColony->$resource)
                                                {
                                                    $hasEnough = false;
                                                    $missingResString .= " ".config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(ceil($upgradePrice[$resource]-$this->player->activeColony->$resource));
                                                }
                                                else
                                                    $this->player->activeColony->$resource -= $upgradePrice[$resource];
                                            }
                                            if(!$hasEnough)
                                            {
                                                $this->paginatorMessage->content = trans('generic.notEnoughResources', ['missingResources' => $missingResString], $this->player->lang);
                                                $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                                $this->closed = true;
                                            }
                                            else
                                            {
                                                $this->player->activeColony->save();
                                                $alliance->player_limit += 1;
                                                $alliance->save();

                                                $this->paginatorMessage->content = trans('alliance.upgradeSuccess', ['newLimit' => $alliance->player_limit], $this->player->lang);
                                                $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                                $this->closed = true;
                                            }
                                            return;
                                        }
                                        elseif($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                        {
                                            $this->paginatorMessage->content = trans('generic.cancelled', [], $this->player->lang);
                                            $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                            $this->closed = true;
                                        }
                                        $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                                    }
                                    catch(\Exception $e)
                                    {
                                        echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                                    }
                                });
                            });
                        }
                        elseif(Str::startsWith('role', $this->args[0]))
                        {
                            if($this->player->alliance->leader_id != $this->player->id)
                                return trans('generic.missingPermission',[],$this->player->lang);

                            if(!isset($this->args[1]) || Str::startsWith('list', $this->args[1]))
                            {
                                //Display List
                                $embed = [
                                    'author' => [
                                        'name' => $this->player->user_name,
                                        'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                                    ],
                                    "title" => trans('alliance.rolesList', [], $this->player->lang),
                                    "description" => trans('alliance.rolesHowTo', [], $this->player->lang),
                                    'fields' => [],
                                    'footer' => array(
                                        'text'  => 'Stargate',
                                    ),
                                ];

                                foreach($this->player->alliance->roles()->orderBy('right_level','desc')->get() as $role)
                                {
                                    if($role->right_recruit == 1)
                                        $recruitRightString = trans('generic.yes', [], $this->player->lang);
                                    else
                                        $recruitRightString = trans('generic.no', [], $this->player->lang);

                                    if($role->right_kick == 1)
                                        $kickRightString = trans('generic.yes', [], $this->player->lang);
                                    else
                                        $kickRightString = trans('generic.no', [], $this->player->lang);

                                    if($role->right_promote == 1)
                                        $promoteRightString = trans('generic.yes', [], $this->player->lang);
                                    else
                                        $promoteRightString = trans('generic.no', [], $this->player->lang);

                                    $embed['fields'][] = array(
                                        'name' => $role->name,
                                        'value' => /*trans('alliance.roleLvl', [], $this->player->lang).": `".$role->right_level."`\n"
                                                   .*/trans('alliance.recruitementRight', [], $this->player->lang).": `".$recruitRightString."`\n"
                                                   .trans('alliance.kickRight', [], $this->player->lang).": `".$kickRightString."`\n"
                                                   .trans('alliance.promoteRight', [], $this->player->lang).": `".$promoteRightString."`\n",
                                        'inline' => true
                                    );
                                }

                                $newEmbed = $this->discord->factory(Embed::class,$embed);
                                $this->message->channel->sendMessage('', false, $newEmbed);

                            }
                            else
                            {
                                if(count($this->args) < 5)
                                    return trans('generic.missingArgs',[],$this->player->lang);

                                $roleEdit = AllianceRole::firstWhere([["alliance_id", $this->player->alliance->id], ['name', 'like', $this->args[1].'%']]);
                                if(is_null($roleEdit))
                                    return trans("alliance.unknownRole", [], $this->player->lang);
                                elseif(Str::startsWith('set', $this->args[2]))
                                {
                                    if(Str::startsWith('name', $this->args[3]))
                                    {
                                        $newRoleName = trim(join(' ', array_slice($this->args, 4)));
                                        if(strlen($newRoleName) < 3)
                                            return trans('generic.nameTooShort', [], $this->player->lang);
                                        if(strlen($newRoleName) > 30)
                                            return trans('generic.nameTooLong', [], $this->player->lang);

                                        $oldRoleName = $roleEdit->name;
                                        $roleEdit->name = $newRoleName;
                                        $messageString = trans("alliance.roleNameChanged", ['oldRole' => $oldRoleName, 'newRole' => $newRoleName], $this->player->lang);
                                    }
                                    elseif(Str::startsWith('recruitement', $this->args[3]))
                                    {
                                        if(Str::startsWith('on', $this->args[4]))
                                        {
                                            $roleEdit->right_recruit = true;
                                            $messageString = trans("alliance.recruitementRightEnabled", [], $this->player->lang);
                                        }
                                        else
                                        {
                                            $roleEdit->right_recruit = false;
                                            $messageString = trans("alliance.recruitementRightDisabled", [], $this->player->lang);
                                        }
                                    }
                                    elseif(Str::startsWith('kick', $this->args[3]))
                                    {
                                        if(Str::startsWith('on', $this->args[4]))
                                        {
                                            $roleEdit->right_kick = true;
                                            $messageString = trans("alliance.kickRightEnabled", [], $this->player->lang);
                                        }
                                        else
                                        {
                                            $roleEdit->right_kick = false;
                                            $messageString = trans("alliance.kickRightDisabled", [], $this->player->lang);
                                        }
                                    }
                                    elseif(Str::startsWith('promote', $this->args[3]))
                                    {
                                        if(Str::startsWith('on', $this->args[4]))
                                        {
                                            $roleEdit->right_promote = true;
                                            $messageString = trans("alliance.promoteRightEnabled", [], $this->player->lang);
                                        }
                                        else
                                        {
                                            $roleEdit->right_promote = false;
                                            $messageString = trans("alliance.promoteRightDisabled", [], $this->player->lang);
                                        }
                                    }
                                    else
                                        return trans('generic.missingArgs',[],$this->player->lang);

                                    $roleEdit->save();
                                    return $messageString;

                                }
                            }
                        }
                        elseif(Str::startsWith('set', $this->args[0]))
                        {
                            if($this->player->alliance->leader_id != $this->player->id)
                                return trans('generic.missingPermission',[],$this->player->lang);

                            if(count($this->args) < 3)
                                return trans('generic.missingArgs',[],$this->player->lang);

                            $alliance = $this->player->alliance;
                            if(Str::startsWith('internal_description', $this->args[1]))
                            {
                                $newDesc = trim(strip_tags(join(' ', array_slice($this->args, 2))));
                                if(strlen($newDesc) > 250)
                                    return trans('generic.descriptionTooLong', ['maxLenght' => 250], $this->player->lang);
                                if(strlen($newDesc) < 5)
                                    return trans('generic.descriptionTooShort', ['minLenght' => 5], $this->player->lang);

                                $alliance->internal_description = $newDesc;
                                $messageString = trans("alliance.internalDescriptionChanged", [], $this->player->lang);
                            }
                            elseif(Str::startsWith('external_description', $this->args[1]))
                            {
                                $newDesc = trim(str_replace("\n\n","",strip_tags(join(' ', array_slice($this->args, 2)))));
                                if(strlen($newDesc) > 250)
                                    return trans('generic.descriptionTooLong', [], $this->player->lang);
                                if(strlen($newDesc) < 5)
                                    return trans('generic.descriptionTooShort', ['minLenght' => 5], $this->player->lang);

                                $alliance->external_description = $newDesc;
                                $messageString = trans("alliance.externalDescriptionChanged", ['maxLenght' => 250], $this->player->lang);
                            }
                            elseif(Str::startsWith('leader', $this->args[1]))
                            {
                                if(preg_match("/[0-9]{18}/", $this->args[2], $playerMatch))
                                {
                                    $newLeader = Player::where('user_id', $playerMatch[0])->first();
                                    if(!is_null($newLeader))
                                    {
                                        if(!is_null($newLeader->alliance) && $newLeader->alliance->id && $this->player->alliance->id)
                                        {
                                            /*Leader actuel devient rang 4 */
                                            $newRole = AllianceRole::where([["alliance_id", $this->player->alliance->id], ['right_level', 4]])->first();
                                            $leadRole = AllianceRole::where([["alliance_id", $this->player->alliance->id], ['right_level', 5]])->first();

                                            $this->player->role_id = $newRole->id;
                                            $this->player->save();

                                            $newLeader->role_id = $leadRole->id;
                                            $newLeader->save();

                                            $alliance->leader_id = $newLeader->id;

                                            $messageString = trans("alliance.leaderChanged", ['newLEader' => $newLeader->untagged_user_name, 'allianceName' => $this->player->alliance->name], $this->player->lang);
                                        }
                                        else
                                            return trans('alliance.playerNotMemberOfThisAlliance',['name' => $newLeader->untagged_user_name, 'allianceName' => $this->player->alliance->name],$this->player->lang);
                                    }
                                    else
                                        return trans('generic.unknownPlayer',[],$this->player->lang);
                                }
                                else
                                    return trans('generic.unknownPlayer',[],$this->player->lang);
                            }
                            elseif(Str::startsWith('recruitement', $this->args[1]))
                            {
                                if(isset($this->args[2]) && Str::startsWith('on', $this->args[2]))
                                {
                                    $alliance->recruitement_status = true;
                                    $messageString = trans("alliance.recruitementEnabled", [], $this->player->lang);
                                }
                                elseif(isset($this->args[2]) && Str::startsWith('off', $this->args[2]))
                                {
                                    $alliance->recruitement_status = false;
                                    $messageString = trans("alliance.recruitementDisabled", [], $this->player->lang);
                                }
                                else
                                    return trans('generic.missingArgs',[],$this->player->lang);
                            }

                            $alliance->save();
                            return $messageString;

                        }
                        elseif(Str::startsWith('invite', $this->args[0]) || Str::startsWith('promote', $this->args[0]) || Str::startsWith('demote', $this->args[0]) || Str::startsWith('kick', $this->args[0]))
                        {
                            if(count($this->args) < 2)
                                return trans('generic.missingArgs',[],$this->player->lang);
                            //check role lvl
                            $roleCheck = $this->player->allianceRole;

                            if(preg_match("/[0-9]{18}/", $this->args[1], $playerMatch))
                            {
                                $memberEdit = Player::where('user_id', $playerMatch[0])->first();
                                if(!is_null($memberEdit))
                                {
                                    if(Str::startsWith('invite', $this->args[0]))
                                    {
                                        if(!$roleCheck->right_recruit)
                                            return trans('generic.missingPermission',[],$this->player->lang);

                                        if(!is_null($memberEdit->alliance))
                                            return trans('alliance.alreadyMemberOfAnAlliance',[],$this->player->lang);

                                        $allianceCheck = $this->player->alliance;
                                        if($allianceCheck->player_limit <= $allianceCheck->members->count())
                                            return trans('alliance.membersLimitReached',[],$this->player->lang);

                                        $inviteMsg = trans('alliance.inviteMessage', ['inviter' => $this->player->user_name, 'invited' => '<@'.$memberEdit->user_id.'>', 'allianceName' => $this->player->alliance->name], $this->player->lang);

                                        $this->maxTime = time()+180;
                                        $this->message->channel->sendMessage($inviteMsg)->then(function ($messageSent) use($memberEdit,$allianceCheck){

                                            $this->closed = false;
                                            $this->paginatorMessage = $messageSent;
                                            $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){
                                                $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){
                                                });
                                            });

                                            $filter = function($messageReaction) use($memberEdit,$allianceCheck){
                                                if($messageReaction->user_id != $memberEdit->user_id || $this->closed == true)
                                                    return false;

                                                if($messageReaction->user_id == $memberEdit->user_id)
                                                {
                                                    try{
                                                        if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                                        {
                                                            $allianceCheck->refresh();
                                                            if($allianceCheck->player_limit <= $allianceCheck->members->count())
                                                            {
                                                                $this->paginatorMessage->content = trans('alliance.membersLimitReached', [], $this->player->lang);
                                                                $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                                                $this->closed = true;
                                                                return;
                                                            }

                                                            $newRole = AllianceRole::where([["alliance_id", $this->player->alliance->id], ['right_level', 1]])->first();
                                                            DB::table('players')->where('id', $memberEdit->id)->update(['alliance_id' => $this->player->alliance->id,'role_id' => $newRole->id, 'user_name' => '['.$this->player->alliance->tag.'] '.$memberEdit->user_name]);

                                                            $this->paginatorMessage->content = trans('alliance.inviteAccepted', ['name'=>$memberEdit->user_name, 'allianceName'=>$allianceCheck->name], $this->player->lang);
                                                            $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                                            $this->closed = true;
                                                        }
                                                        elseif($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                                        {
                                                            $this->paginatorMessage->content = trans('alliance.inviteRefused', ['name'=>$memberEdit->user_name], $this->player->lang);
                                                            $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                                            $this->closed = true;
                                                        }
                                                        $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                                                    }
                                                    catch(\Exception $e)
                                                    {
                                                        echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                                                    }
                                                    return true;
                                                }
                                                else
                                                    return false;
                                            };
                                            $this->paginatorMessage->createReactionCollector($filter,['time' => config('stargate.maxCollectionTime')]);
                                        });
                                    }
                                    elseif(!is_null($memberEdit->alliance) && $memberEdit->alliance->id == $this->player->alliance->id)
                                    {
                                        if(Str::startsWith('promote', $this->args[0]))
                                        {
                                            if(!$roleCheck->right_promote && ($roleCheck->right_level+1) > $memberEdit->allianceRole->right_level)
                                                return trans('generic.missingPermission',[],$this->player->lang);

                                            if($memberEdit->allianceRole->right_level < 4)
                                            {
                                                $newRole = AllianceRole::where([["alliance_id", $this->player->alliance->id], ['right_level', ($memberEdit->allianceRole->right_level+1)]])->first();
                                                DB::table('players')->where('id', $memberEdit->id)->update(['role_id' => $newRole->id]);
                                                return trans('alliance.memberPromoted',['name' => $memberEdit->untagged_user_name, 'newRole' => $newRole->name],$this->player->lang);
                                            }
                                            else
                                                return trans('alliance.onlyOneLeader', [],$this->player->lang);
                                        }
                                        elseif(Str::startsWith('demote', $this->args[0]))
                                        {
                                            if(!$roleCheck->right_promote || $memberEdit->allianceRole->right_level >= 4)
                                                return trans('generic.missingPermission',[],$this->player->lang);

                                            if($memberEdit->allianceRole->right_level > 1)
                                            {
                                                $newRole = AllianceRole::where([["alliance_id", $this->player->alliance->id], ['right_level', ($memberEdit->allianceRole->right_level-1)]])->first();
                                                DB::table('players')->where('id', $memberEdit->id)->update(['role_id' => $newRole->id]);
                                                return trans('alliance.memberDemoted',['name' => $memberEdit->untagged_user_name, 'newRole' => $newRole->name],$this->player->lang);
                                            }
                                            else
                                                return trans('alliance.alreadyBottomRank', [],$this->player->lang);
                                        }
                                        elseif(Str::startsWith('kick', $this->args[0]))
                                        {
                                            if(!$roleCheck->right_kick || $this->player->id == $memberEdit->id || $this->player->alliance->leader->id == $memberEdit->id)
                                                return trans('generic.missingPermission',[],$this->player->lang);

                                            DB::table('players')->where('id', $memberEdit->id)->update(['alliance_id' => null,'role_id' => null,'user_name' => $memberEdit->untagged_user_name]);
                                            return trans('alliance.memberKicked',['name' => $memberEdit->untagged_user_name, 'allianceName' => $this->player->alliance->name],$this->player->lang);
                                        }
                                    }
                                    else
                                        return trans('alliance.playerNotMemberOfThisAlliance', ['name' => $memberEdit->user_name, 'allianceName' => $this->player->alliance->name],$this->player->lang);
                                }
                                else
                                    return trans('generic.unknownPlayer',[],$this->player->lang);
                            }
                            else
                                return trans('generic.unknownPlayer',[],$this->player->lang);
                        }
                    }
                }
                catch(\Exception $e)
                {
                    echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                    return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                }


            }
            else
                return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
        }
        catch(\Exception $e)
        {

            return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
        }
    }

    public function getPage()
    {
        $displayList = $this->allianceList->skip(5*($this->page -1))->take(5);

        $embed = [
            'author' => [
                'name' => $this->player->user_name,
                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
            ],
            "title" => trans('alliance.allianceList', [], $this->player->lang),
            "description" => "",/*trans('alliance.genericHowTo', [], $this->player->lang)*/
            'fields' => [],
            'footer' => array(
                //'icon_url'  => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png',
                'text'  => 'Stargate - '.trans('generic.page', [], $this->player->lang).' '.$this->page.' / '.$this->maxPage,
            ),
        ];

        foreach($displayList as $alliance)
        {
            $externalDesc = "/";
            if(!is_null($alliance->external_description))
                $externalDesc = $alliance->external_description;

            if($alliance->recruitement_status == 1)
                $recrutementStatusString = trans('generic.yes', [], $this->player->lang);
            else
                $recrutementStatusString = trans('generic.no', [], $this->player->lang);

            $embed['fields'][] = array(
                'name' => '['.$alliance->tag.'] '.$alliance->name,
                'value' => "__".trans('alliance.leader', [], $this->player->lang)."__: ".$alliance->leader->untagged_user_name."\n"
                ."__".trans('alliance.membersCount', [], $this->player->lang)."__: ".number_format($alliance->members->count())."\n"
                ."__".trans('alliance.recruitementStatus', [], $this->player->lang)."__: ".$recrutementStatusString."\n"
                ."__".trans('alliance.externalDescription', [], $this->player->lang)."__: \n".$externalDesc."\n",
                'inline' => false
            );
        }

        return $embed;
    }

    public function getUpgradePrice($actualLimit)
    {
        $upgradeBasePrice = config('stargate.alliance.baseUpgradePrice') * pow(2,($actualLimit-config('stargate.alliance.baseMembers')));

        $upgradePrice = [];
        foreach (config('stargate.resources') as $resource)
        {
            $upgradePrice[$resource] = $upgradeBasePrice;
        }
        return $upgradePrice;
    }
}
