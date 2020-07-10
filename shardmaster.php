<?php

error_reporting(E_ALL);

ini_set('memory_limit', '-1');

//---------------------

//Includes/Requires

include __DIR__ . '/vendor/autoload.php';

include 'db.php';

use Discord\DiscordCommandClient;

use GuzzleHttp\Client as HttpClient;


global $db,

    $messageId,

    $waitingForMessage,

    $autoTimer,

    $shardsInfos,

    $timeModifier,

    $realTimers,

    $channelIds,

    $messageIdTop,

    $messageIdSecondaryTop,

    $messageIdSecondayTimer,

    $waitingForMessageTop,

    $waitingForMessageSecondaryTop,

    $waitingForSecondaryMessage,

    $externalNotifs,

    $messageIdThirdTimer,

    $waitingForThirdMessage,

    $guildListInt,

    $guildListExt,

    $authorizedUser,

    $repIdWaiting;


$authorizedUser = array(

    '125641223544373248' => array('limit' => 99999),

    '280278648517296128' => array('limit' => 2),

    '298607016480473089' => array('limit' => 14),/*,

                                'notif' =>

                                  array('' => '563442279994490881',

                                        '' => '563417281095270410',

                                        '' => '563446514035195914',

                                        '' => '556842027778703361',

                                        '' => '563417374322065443',

                                        '' => '563418869146845185',

                                        '' => '563418938826817547',

                                        '' => '563417337504727040',

                                        '' => '563439186103894032',

                                        '' => '563446725621317633',

                                        '' => '563439532880560138',

                                        '' => '563418831717138442',

                                        '' => '563417424234414110')

                                        */

    '420716089987563540' => array('limit' => 2),

    '261887460114563072' => array('limit' => 1)


);


$externalNotifs = array();


$messageId = $messageIdSecondayTimer = $messageIdTop = $messageIdSecondaryTop = $messageIdThirdTimer = 0;

$timeModifier = "";

$channelID = 633209568423444491;
$channelIDBis = 646383556318199819;
$channelIDTri = 667378623312953345;


$waitingForMessage = $waitingForThirdMessage = $waitingForSecondaryMessage = $waitingForMessageSecondaryTop = $autoTimer = false;

$shardsinfos = $realTimers = array();

$db = new db();

$guildListInt = $guildListExt = array();


//Initiate

//---------------------

//Set mandatory bits

global $discord;

$discord = new DiscordCommandClient([

    'token' => 'NzMwODE1Mzg4NDAwNjE1NDU1.Xwc_Dg.9GJ5Mww-YtAeQZZ-2C9MR3EWn2c',

    'prefix' => '!',

    'name' => 'ShardMaster',

    'description' => 'ShardMaster'

]);


try {

    function loadFromDB()

    {

        global $db, $shardsinfos;

        $shardsRequest = $db->query('SELECT * FROM shards')->fetchAll();

        if (!empty($shardsRequest)) {

            if (!empty($shardsinfos))

                $shardsinfos = array();

            foreach ($shardsRequest as $shardRequest) {

                $shardRequest['last_warning'] = new DateTime($shardRequest['last_warning']);

                $shardRequest['last_update'] = new DateTime($shardRequest['last_update']);

                $shardRequest['current_timer'] = new DateTime($shardRequest['current_timer']);

                $shardsinfos[$shardRequest['id']] = $shardRequest;

                $shardsinfos[$shardRequest['id']]['warning_flag'] = false;

                $shardsinfos[$shardRequest['id']]['real_timer'] = true;

                $shardsinfos[$shardRequest['id']]['string_date'] = '';
            }
        }

        loadGuilds();
    }


    function getTop($flagExt)

    {

        global $db, $discord;

        $replyContent = "

:trophy: Top local des trésors victorieux :trophy:\n";


        $timeNow = new DateTime("NOW");

        $topRequest = $db->query('SELECT COUNT(*) as victories,user_name FROM `tr_winners` WHERE flag_ext = ? GROUP BY `user_id` ORDER BY `victories` DESC LIMIT 20', $flagExt)->fetchAll();


        for ($cptTop = 0; $cptTop < count($topRequest); $cptTop++) {

            $addRep = "";

            if ($flagExt == 0) {

                switch ($cptTop) {

                    case 0:

                        $addRep = ":crown: ";

                        break;

                    case 1:

                        $addRep = "<:cturtle:557301272013701190> ";

                        break;

                    case 2:

                        $addRep = ":third_place: ";

                        break;
                }

                if ($cptTop == count($topRequest) - 1)

                    $addRep = "<:pepe:557301286479855617> ";
            }

            $replyContent .= "\n " . ($cptTop + 1) . ". " . $addRep . $topRequest[$cptTop]['user_name'] . " - " . $topRequest[$cptTop]['victories'] . " trésors";
        }

        if ($flagExt == 0)

            $comptabiliseDepuis = "Comptabilisés depuis le 17/03/19";

        else

            $comptabiliseDepuis = "Comptabilisés depuis le 09/04/19";

        $replyContent .= "\n\n:clock2: Dernière maj: " . $timeNow->format("H:i:s") . " || " . $comptabiliseDepuis;


        return completementCon($replyContent);
    }


    function timersReset()

    {

        global $shardsinfos;

        foreach ($shardsinfos as $info) {

            $shardsinfos[$info['id']]['current_timer'] = new DateTime("NOW");

            $shardsinfos[$info['id']]['last_update'] = new DateTime("NOW");
        }
    }


    function completementCon($var)

    {

        return $var;
    }


    function loadRealTimersDatas()

    {

        global $realTimers, $shardsinfos;

        $timeNow = new DateTime("NOW");

        foreach ($shardsinfos as $info) {

            $realTimers[$info['premium_channel_id']] = array('reload' => true, 'message_id' => 0, 'message_mention_id' => 0, 'shardId' => $info['id'], 'reload_date' => $timeNow);
            $realTimers[646383556318199819] = array('reload' => true, 'message_id' => 0, 'message_mention_id' => 0, 'shardId' => $info['id'], 'reload_date' => $timeNow);
            $realTimers[667378623312953345] = array('reload' => true, 'message_id' => 0, 'message_mention_id' => 0, 'shardId' => $info['id'], 'reload_date' => $timeNow);

            //$realTimers[$info['premium2_channel_id']] = array('reload' => true, 'message_id' => 0,'message_mention_id' => 0,'shardId'=>$info['id'],'reload_date' => $timeNow);

            //$realTimers[$info['channel_id']] = array('reload' => true, 'message_id' => 0,'shardId'=>$info['id'],'reload_date' => $timeNow);

        }
    }


    function loadGuilds()

    {

        global $db, $guildListInt, $guildListExt;

        $guildListExt = $guildListInt = array();

        $guildRequest = $db->query("SELECT * FROM servers")->fetchAll(); //servers

        if (!empty($guildRequest)) {

            foreach ($guildRequest as $guild) {

                if (in_array($guild['guild_owner'], array(125641223544373248, 280278648517296128)))

                    $guildListInt[$guild['guild_id']] = $guild;

                else

                    $guildListExt[$guild['guild_id']] = $guild;
            }
        }
    }


    function saveToDB()

    {

        global $db, $shardsinfos;

        foreach ($shardsinfos as $infos) {

            $check = $db->query(
                'UPDATE shards SET last_warning=?,last_update=?,current_timer=?,message_id=?,premium_message_id=? WHERE id=?',

                $infos['last_warning']->format("Y-m-d H:i:s"),

                $infos['last_update']->format("Y-m-d H:i:s"),

                $infos['current_timer']->format("Y-m-d H:i:s"),

                $infos['message_id'],

                $infos['premium_message_id'],

                $infos['id']
            );
        }
    }


    function getTimers()

    {

        global $shardsinfos;


        $sortedShards = array();

        foreach ($shardsinfos as $info) {

            $sortedShards[$info['id']] = $info['current_timer'];
        }


        asort($sortedShards);


        $replyContent = "```Markdown

Prochains timers: ";


        foreach ($sortedShards as $id => $timer) {

            $suppspace = "";

            if (is_a($timer, 'DateTime')) {

                $timeNow = new DateTime('NOW');


                if ($timeNow < $timer) {

                    $interval = $timeNow->diff($timer);

                    $timerString = $interval->format("Y-m-d H:i:s");


                    $seconds = $interval->format("%s");

                    if (strlen($seconds) < 2)

                        $seconds = "0" . $seconds;

                    $minutes = $interval->format("%i");

                    if (strlen($minutes) < 2)

                        $minutes = "0" . $minutes;

                    $timerToShow = $minutes . "m" . $seconds . "s";

                    if (strlen($timerToShow) < 6)

                        $timerToShow .= " ";


                    if ($id < 10)

                        $suppspace = " ";

                    $timerToShow = $timerToShow . " || " . $timer->format("H:i:s");
                } else {

                    $timerToShow = "Pas de >tr, pas de chocolat...";
                }


                if ($id < 10)

                    $suppspace = " ";


                $replyContent .= "\n " . $suppspace . $id . " => " . $timerToShow;
            } else {


                if ($id < 10)

                    $suppspace = " ";


                $replyContent .= "\n " . $suppspace . $id . " => " . $timer;
            }
        }


        $replyContent .= "```";


        return $replyContent;
    }


    function getTimersShort()

    {

        global $shardsinfos, $discord, $channelID, $channelIDBis, $channelIDTri, $realTimers;


        $sortedShards = array();

        foreach ($shardsinfos as $info) {

            $sortedShards[$info['id']] = $info['current_timer'];
        }
        $channelID = 633209568423444491;

        $channelIDBis = 646383556318199819;
        $channelIDTri = 667378623312953345;



        asort($sortedShards);


        $replyContent = "```Markdown

Prochains timers: "; //(complete list: <#555019107523887124>)


        $cptTimer = 0;

        foreach ($sortedShards as $id => $timer) {


            $suppspace = "";

            if (is_a($timer, 'DateTime')) {


                $timeNow = new DateTime('NOW');


                if ($timeNow < $timer) {

                    $cptTimer++;
                    if ($cptTimer > 3)
                        break;


                    $interval = $timeNow->diff($timer);

                    $timerString = $interval->format("Y-m-d H:i:s");


                    $seconds = $interval->format("%s");

                    if (strlen($seconds) < 2)

                        $seconds = "0" . $seconds;

                    $minutes = $interval->format("%i");

                    if (strlen($minutes) < 2)

                        $minutes = "0" . $minutes;

                    $timerToShow = $minutes . "m" . $seconds . "s";

                    if (strlen($timerToShow) < 6)

                        $timerToShow .= " ";


                    if ($id < 10)

                        $suppspace = " ";

                    $timerToShow = $timerToShow . " || " . $timer->format("H:i:s");


                    if ($minutes < 1 && $seconds <= 20) {

                        if ($shardsinfos[$id]['warning_flag']) {


                            $guild = $discord->guilds->get('id', 554645551501541377);

                            $channel = $guild->channels->get('id', 633209568423444491);


                            $role = $guild->roles->get("name", "Notif");

                            $mentions = " <@&" . $role->id . "> ";

                            if (isset($realTimers[$channelID]['message_mention_id']) && !empty($realTimers[$channelID]['message_mention_id']))

                                $channel->deleteMessage($realTimers[$channelID]['message_mention_id']);

                            echo PHP_EOL . $timeNow->format("H:i:s") . " Shard " . $id . " dans moins de 20 secondes" . $mentions;

                            $channel->sendMessage(" Un shard va terminer!" . $mentions)->then(function ($messageHMen) use ($channel) {

                                global $realTimers, $shard, $channelID;

                                $realTimers[$channelID]['message_mention_id'] = $messageHMen->id;



                                $channel->deleteMessage($messageHMen->id);
                            }, function ($e) {

                                print_r($e->getMessage());
                            });




                            $shardsinfos[$id]['last_warning'] = $timeNow;

                            $shardsinfos[$id]['warning_flag'] = false;
                        }
                    } elseif (!$shardsinfos[$id]['warning_flag']) {

                        $shardsinfos[$id]['warning_flag'] = true;
                    }
                } else {

                    $timerToShow = "Pas de >tr, pas de chocolat...";
                }


                if ($id < 10)

                    $suppspace = " ";


                $replyContent .= "\n " . $suppspace . $id . " => " . $timerToShow;
            } else {


                if ($id < 10)

                    $suppspace = " ";


                $replyContent .= "\n " . $suppspace . $id . " => " . $timer;
            }
        }


        $replyContent .= "```";

        if ($cptTimer == 0)
            $replyContent = "Please use `>tr t` (Pas d'bras.. pas d'chocolat!)";


        return $replyContent;
    }


    //Status and grab info

    $discord->on('ready', function ($discord) {
        $game = $discord->factory(Game::class, [
            'name' => 'test'
        ]);
        
        $discord->updatePresence($game);

        $discord->on('MESSAGE_CREATE', function ($message) {

            global $realTimers, $db, $discord, $guildListInt;

/*
            if ($message->author->id != 517045134714470400 && isset($realTimers[$message->channel_id]) && $realTimers[$message->channel_id]['reload'] == false) {

                $realoadDate = new DateTime();

                $realoadDate->modify("+2 seconds");

                $realTimers[$message->channel_id]['reload_date'] = $realoadDate;

                $realTimers[$message->channel_id]['reload'] = true;
            }

*/
            //@Kiliwick - Votre point de réputation a été donné avec succès à @Thorrdu !

/*
            if ($message->author->id == 280726849842053120 && (strstr($message->content, "GG ! Vous venez de gagner") || strstr($message->content, "GG ! You just won the treasure"))) {

                preg_match_all(
                    "/[0-9]{18}/", //"/(<@)+[0-9]{18}+(>)/"

                    $message->content,

                    $matches,
                    PREG_PATTERN_ORDER
                );

                if (!empty($matches)) {

                    //print_r($matches);

                    $winner = $matches[0][0];

                    $idShard = 0;

                    $flagExt = 0;


                    if (isset($guildListInt[$message->guild_id])) {

                        $guild = $discord->guilds->get('id', $message->guild_id);

                        $member = $guild->members[$winner];

                        if (!empty($member)) {

                            $pos = strpos($message->content, 'shard ');

                            if ($pos != false) {

                                $idShard = (int) trim(str_replace(array('|', ',', ' ', 'd', 'GG'), '', substr(str_replace('[VIRTUAL] ', '', $message->content), ($pos + 6), 6)));
                            }
                        }
                        //$idShard = $realTimers[$message->channel_id]['shardId'];
                    } else {
                        $guild = $discord->guilds->get('id', $message->guild_id);
                        $member = $guild->members[$winner];
                        $pos = strpos($message->content, 'shard ');
                        if ($pos != false) {
                            $idShard = (int) trim(str_replace(array('|', ',', ' ', 'd', 'GG'), '', substr(str_replace('[VIRTUAL] ', '', $message->content), ($pos + 6), 6)));
                        }
                        $flagExt = 1;
                    }

                    //echo 'pseudo avant: '.$member->user_name.PHP_EOL;
                    $memberUserName = preg_replace("/[^a-zA-Z0-9_\s]/", '', $member->user_name);
                    //echo 'pseudo apres: '.$memberUserName.PHP_EOL;

                    if (empty($memberUserName) || strlen($memberUserName) <= 3)
                        $memberUserName = 'pseudo illisible/cancer';

                    if (!empty($idShard) || $idShard == 0) {

                        $db->query('INSERT INTO tr_winners (user_id,user_name,shard_id,guild_id,flag_ext) VALUES (?,?,?,?,?)', $winner, $memberUserName, $idShard, $message->guild_id, $flagExt);


                        if ($flagExt == 1) {

                        } else {

                            $guild = $discord->guilds->get('id', 554645551501541377);

                            $channel = $guild->channels->get('id', 556635839271010324);

                            $replyContent = "```Markdown";

                            $dateVictory = new DateTime("NOW");

                            $replyContent .= completementCon("\n" . $dateVictory->format('H:i:s') . " {$member->user_name} remporte le trésor du Shard " . $idShard . "! ```");


                            $channel->sendMessage($replyContent);
                        }
                    } else {

                        echo 'Shard pas bon : ' . $message->content . PHP_EOL;
                    }
                }
            }*/
        });


        $discord->on('message', function ($message, $discord) {

            
            /*
            global $shardsinfos, $waitingForMessage, $messageIdThirdTimer, $waitingForThirdMessage, $waitingForMessageTop, $messageId, $messageIdTop, $messageIdSecondaryTop, $waitingForSecondaryMessage, $messageIdSecondayTimer, $waitingForMessageSecondaryTop;

            $msg = trim($message->content);

            $cmd = strtolower($msg);

            if (strlen($msg) <= 2) {

                return;
            }

            if ($waitingForMessage && $message->author->id == 517045134714470400 && $message->channel_id = 555019107523887124) {

                $waitingForMessage = false;

                $messageId = $message->id;
            }

            if ($message->author->id == 280726849842053120 && (strstr($msg, 'it will be available again in : ') || strstr($msg, 'actuellement en train de combattre sur le') || (strstr($msg, '- **') && strstr($msg, '** *(')))) {

                $posBkp = false;

                $shardDetected = 0;

                $pos = strpos($message->content, 'shard ');

                if ($pos != false) {

                    $shardDetected = (int) trim(str_replace(array('|', ',', ' ', 'd'), '', substr(str_replace('[VIRTUAL] ', '', $message->content), ($pos + 6), 6)));
                } else {

                    $posBkp = strpos($message->content, '[S');

                    if ($posBkp != false) {

                        $shardDetected = (int) trim(str_replace(']', '', substr(str_replace('[VIRTUAL] ', '', $message->content), ($posBkp + 2), 2)));
                    }

                    $posBkp2 = strpos($message->content, '<S');

                    if ($posBkp2 != false) {

                        $shardDetected = (int) trim(str_replace(array(']', '>'), '', substr(str_replace('[VIRTUAL] ', '', $message->content), ($posBkp2 + 2), 2)));
                    }
                }


                $reg = '/[0-5]?[0-9]?m?[0-5]?[0-9]s/';

                $match = preg_match_all($reg, $message->content, $matches);

                $date = '';


                if ($match && (!empty($shardDetected) || $shardDetected == 0)) {

                    if (count($matches[0]) == 1) {

                        $date = str_replace('s', '', $matches[0][0]);
                    } elseif (count($matches[0]) == 2) {

                        $date = str_replace('s', '', $matches[0][1]);
                    }


                    if (!empty($date)) {

                        if (strstr($date, 'm')) {

                            $arrayDate = explode('m', $date);

                            $minutes = (int) $arrayDate[0];

                            $seconds = (int) $arrayDate[1];
                        } else {

                            $minutes = 0;

                            $seconds = (int) $date;
                        }


                        if (($minutes == 0 && $seconds < 10) || ($posBkp != false && $minutes == 0)) {

                            //Pas de prise en compte des spam de fin

                        } else {

                            $newShardTimer = new DateTime(date("Y-m-d H:i:s"));

                            $newShardTimer->modify('+' . $minutes . ' minutes');

                            $newShardTimer->modify('+' . $seconds . ' seconds');

                            $newShardTimer->modify("-1 seconds");


                            $newtimerDate = $newShardTimer->format("Y-m-d H:i:s");

                            $newtimerToShow = $newShardTimer->format("H:i:s");

                            if ($shardsinfos[$shardDetected]['string_date'] != $date) {

                                $shardsinfos[$shardDetected]['current_timer'] = $newShardTimer;

                                $shardsinfos[$shardDetected]['string_date'] = $date;
                            }

                            $shardsinfos[$shardDetected]['last_update'] = new DateTime("NOW");
                        }
                    }
                }
            }

            if ($message->author->id == 280726849842053120 && strstr($msg, 'Shard 1') && strstr($msg, 'Shard 10') && strstr($msg, 'Shard 16') && strstr($msg, 'Shard 0')) {

                $shardDetected = 0;

                $timerList = explode("\n", $msg);


                foreach ($timerList as $timerLine) {

                    $shardDetail = explode(':', $timerLine);

                    $shardDetected = trim(str_replace('Shard ', '', $shardDetail[0]));

                    $shardTimer = trim($shardDetail[1]);


                    $date = '';

                    if (true) //$shardDetected != 0)

                    {

                        $date = str_replace(array('s', '0h'), '', $shardTimer);


                        if (!empty($date)) {

                            if (strstr($date, 'm')) {




                                $arrayDate = explode('m', $date);

                                $minutes = (int) $arrayDate[0];

                                $seconds = (int) $arrayDate[1];
                            } else {

                                $minutes = 0;

                                $seconds = (int) $date;
                            }


                            if (($minutes == 0 && $seconds < 10)) {

                                //Pas de prise en compte des spam de fin

                            } else {

                                $newShardTimer = new DateTime(); //date("Y-m-d H:i:s")

                                $newShardTimer->modify('+' . $minutes . ' minutes');

                                $newShardTimer->modify('+' . $seconds . ' seconds');

                                $newShardTimer->modify("-1 seconds");

                                $newtimerDate = $newShardTimer->format("Y-m-d H:i:s");

                                $newtimerToShow = $newShardTimer->format("H:i:s");

                                if ($shardsinfos[$shardDetected]['string_date'] != $date) {

                                    $shardsinfos[$shardDetected]['current_timer'] = $newShardTimer;

                                    $shardsinfos[$shardDetected]['string_date'] = $date;
                                }

                                $shardsinfos[$shardDetected]['last_update'] = new DateTime("NOW");
                            }
                        }
                    }
                }
            }
            */
        });





    });



    $discord->registerCommand('testReturn', function ($message) use ($discord) {

        
        $message->channel->sendMessage('test')->then(
            function ($response) /*use ($deferred)*/ {
                print_r($response);

                $response->react('🥓');
            }
        );
        


    }, [

        'description' => 'Starting RealTimeTimer',

    ]);
    

    $discord->registerCommand('edit', function ($message) use ($discord) {

        $args = array();
        if (strstr($message->content, ' '));
        $args = explode(' ', $message->content);


        if (count($args) > 2) {

            $messId = $args[1];
            $substrLen = strlen($args[0])+strlen($args[1])+1;
            $newMess = substr($message->content,$substrLen,strlen($message->content));

            $message->channel->editMessage($messId, $newMess);
            echo 'OK';
        }
        else
        {
            echo 'PAS OK';
        }

    }, [

        'description' => 'Starting RealTimeTimer',

    ]);
    
    $discord->registerCommand('test', function ($message) use ($discord) {


        $message->channel->sendMessage('test');
/*
        if (in_array($message->author->id, array(125641223544373248))) {

            global $messageId, $waitingForMessage, $autoTimer;


            loadFromDB();
        }*/
    }, [

        'description' => 'Starting RealTimeTimer',

    ]);


    $discord->registerCommand('init', function ($message) use ($discord) {

        if (in_array($message->author->id, array(125641223544373248))) {

            global $messageId, $waitingForMessage, $autoTimer;


            loadFromDB();
        }
    }, [

        'description' => 'Starting RealTimeTimer',

    ]);




    $discord->registerCommand('save', function ($message) {

        if (in_array($message->author->id, array(125641223544373248))) {

            $message->channel->sendMessage("Saving datas...");

            saveToDB();
        }
    }, [

        'description' => 'Saving to DB',

    ]);


    $discord->loop->addPeriodicTimer(1, function () use ($discord) {

        global $messageId, $messageIdSecondayTimer, $messageIdThirdTimer;

        if (!empty($messageId)) {

            $guild = $discord->guilds->get('id', 554645551501541377);

            $channel = $guild->channels->get('id', 555019107523887124);

            $channel->patchMessage(getTimers(), $messageId);
        }
    });


    /*633209568423444491*/

    /*NEW TIMER*/

/*
    $discord->loop->addPeriodicTimer(1, function () use ($discord) {

        global $autoTimer, $realTimers;


        if ($autoTimer) {

            $timeNow = new DateTime('NOW');


            $guild = $discord->guilds->get('id', 554645551501541377);

            $channel = $guild->channels->get('id', 633209568423444491);
            $channelBis = $guild->channels->get('id', 646383556318199819);
            $channelTri = $guild->channels->get('id', 667378623312953345);


            $channelID = 633209568423444491;

            $channelIDBis = 646383556318199819;
            $channelIDTri = 667378623312953345;



            $replyContent = getTimersShort();


            if ($realTimers[$channelID]['reload'] && $realTimers[$channelID]['reload_date'] < $timeNow) {

                if (!empty($realTimers[$channelID]['message_id']))

                    $channel->deleteMessage($realTimers[$channelID]['message_id']);


                $channel->sendMessage($replyContent)->then(function ($messageH) use ($channelID) {

                    global $realTimers;

                    $realTimers[$channelID]['message_id'] = $messageH->id;
                }, function ($e) {

                    print_r($e->getMessage());
                });

                $realTimers[$channelID]['reload'] = false;
            } else {

                $channel->patchMessage($replyContent, $realTimers[$channelID]['message_id']);
            }



            if ($realTimers[$channelIDBis]['reload'] && $realTimers[$channelIDBis]['reload_date'] < $timeNow) {

                if (!empty($realTimers[$channelIDBis]['message_id']))

                    $channelBis->deleteMessage($realTimers[$channelIDBis]['message_id']);


                $channelBis->sendMessage($replyContent)->then(function ($messageH) use ($channelIDBis) {

                    global $realTimers;

                    $realTimers[$channelIDBis]['message_id'] = $messageH->id;
                }, function ($e) {

                    print_r($e->getMessage());
                });

                $realTimers[$channelIDBis]['reload'] = false;
            } else {

                $channelBis->patchMessage($replyContent, $realTimers[$channelIDBis]['message_id']);
            }




            if ($realTimers[$channelIDTri]['reload'] && $realTimers[$channelIDTri]['reload_date'] < $timeNow) {

                if (!empty($realTimers[$channelIDTri]['message_id']))

                    $channelTri->deleteMessage($realTimers[$channelIDTri]['message_id']);


                $channelTri->sendMessage($replyContent)->then(function ($messageH) use ($channelIDTri) {

                    global $realTimers;

                    $realTimers[$channelIDTri]['message_id'] = $messageH->id;
                }, function ($e) {

                    print_r($e->getMessage());
                });

                $realTimers[$channelIDTri]['reload'] = false;
            } else {

                $channelTri->patchMessage($replyContent, $realTimers[$channelIDTri]['message_id']);
            }
        }


        if (!empty($messageId)) {

            $guild = $discord->guilds->get('id', 554645551501541377);

            $channel = $guild->channels->get('id', 555019107523887124);

            $channel->patchMessage(getTimers(), $messageId);
        }
    });
*/
/*
    $discord->loop->addPeriodicTimer(600, function () use ($discord) {

        global $messageIdTop, $messageIdSecondaryTop;

        if (!empty($messageIdTop)) {

            $guild = $discord->guilds->get('id', 554645551501541377);

            $channel = $guild->channels->get('id', 556926235964473392);

            $channel->patchMessage(getTop(0), $messageIdTop);
        }
    });
*/
/*
    $discord->loop->addPeriodicTimer(60, function () use ($discord) {

        global $db;

        $timeNow = date("Y-m-d H:i:s");

        $reminders = $db->query("SELECT * FROM reminders WHERE date_reminder < ? ", $timeNow)->fetchAll();

        if (!empty($reminders)) {

            foreach ($reminders as $reminder) {

                foreach ($discord->guilds as $guild) {

                    if (isset($guild->members[$reminder['user_id']])) {

                        $member = $guild->members[$reminder['user_id']];

                        $member->user->sendMessage(completementCon("Oh, <@" . $reminder['user_id'] . ">! Tu m'avais demandé de te rappeler de faire ton **" . $reminder['reason'] . "**"));

                        $db->query('DELETE FROM reminders WHERE id=?', $reminder['id']);

                        break;
                    }
                }
            }
        }
    });
*/
/*
    $discord->loop->addPeriodicTimer(120, function () use ($discord) {

        saveToDb();
    });
*/

    $discord->registerCommand('timerstart', function ($message) {

        global $guildListInt;

        if (isset($guildListInt[$message->guild_id])) {

            global $messageId, $waitingForMessage, $discord;

            $guild = $discord->guilds->get('id', 554645551501541377);

            if (in_array($message->author->id, array(125641223544373248)) || checkRole($guild->members->get('id', $message->author->id), "Modo")) {

                if ($message->channel_id = 555019107523887124) {

                    if (!empty($messageId)) {

                        $messageId = 0;
                    }

                    $waitingForMessage = true;

                    $message->channel->sendMessage(getTimers());
                } else {

                    $message->channel->sendMessage("Pas le bon chan...");
                }
            }
        }
    }, [

        'description' => 'Starting RealTimeTimer',

    ]);


    $discord->registerCommand('timerstop', function ($message) {

        global $guildListInt;

        if (isset($guildListInt[$message->guild_id])) {

            global $messageId, $waitingForMessage, $discord;

            $guild = $discord->guilds->get('id', 554645551501541377);

            if (in_array($message->author->id, array(125641223544373248)) || checkRole($guild->members->get('id', $message->author->id), "Modo")) {

                $messageId = 0;

                $message->channel->sendMessage('Timer OFF');
            }
        }
    }, [

        'description' => 'Stopping RealTimeTimer',

    ]);


    $discord->registerCommand('timerReset', function ($message) {

        global $guildListInt;

        if (isset($guildListInt[$message->guild_id])) {

            global $messageId, $waitingForMessage, $discord;

            $guild = $discord->guilds->get('id', 554645551501541377);

            if (in_array($message->author->id, array(125641223544373248)) || checkRole($guild->members->get('id', $message->author->id), "Modo")) {

                timersReset();

                $message->channel->sendMessage('Timers RESET');
            }
        }
    }, [

        'description' => 'Stopping RealTimeTimer',

    ]);


    $discord->registerCommand('halp', function ($message) {

        global $guildListInt;

        if (isset($guildListInt[$message->guild_id])) {

            global $discord;


            $replyContent = "```Markdown

";


            $guild = $discord->guilds->get('id', 554645551501541377);

            $args = array();

            if (strstr($message->content, ' '));

            $args = explode(' ', $message->content);

            if (count($args) == 1) {

                //Commandes Premium

                //$replyContent .= "Commandes PREMIUM";

                //$replyContent .= "\n!t => Affichez le tableau global des Shards";

                //Commandes Classiques

                $replyContent .= "\n\nCommandes MEMBRE";

                $replyContent .= "\n!rmd => Permet d'ajouter ou supprimer des rappels. !help rmd pour plus d'infos.";

                $replyContent .= " ```";

                $message->channel->sendMessage(completementCon($replyContent));
            } elseif (count($args) > 1 && $args[1] == "rmd") {


                $replyContent .= "Utilisation de la commande !rmd";

                $replyContent .= "\n!rmd [commande] => ajoute un rappel pour votre prochaine commande.";

                $replyContent .= "\nExemples: !rmd daily ou encore !rmd hr";

                $replyContent .= "\n\n!rmd [durée] [raison] => Ajoute un rappel dans [durée]";

                $replyContent .= "\nExemple: !rmd 1h20m lance >mine => Le bot vous rappellera 'lance >mine' dans 1h20m";

                $replyContent .= "\nFormat reconnu: 1y1d1h1m";


                $replyContent .= "\n\n!rmd list => affiche la liste de tes rappels en attente";

                $replyContent .= "\n!rmd remove ID => Supprime un rappel en attente";

                $replyContent .= "\n\nLes rappels se font actuellement dans le chan #commandes-bot";


                $replyContent .= " ```";

                $message->channel->sendMessage(completementCon($replyContent));
            }
        } else {

            global $discord;


            $replyContent = "```Markdown

";


            $guild = $discord->guilds->get('id', 554645551501541377);

            $args = array();

            if (strstr($message->content, ' '));

            $args = explode(' ', $message->content);

            if (count($args) == 1) {

                $replyContent .= "\n!register => Autorise la sauvegarde des victoires >tr sur ce serveur.";

                // $replyContent .= "\n!t => Affichez le tableau global des Shards";

                $replyContent .= "\n!rmd => Permet d'ajouter ou supprimer des rappels. !help rmd pour plus d'infos.";

                $replyContent .= " ```";

                $message->channel->sendMessage(completementCon($replyContent));
            } elseif (count($args) > 1) {

                if ($args[1] == "rmd") {

                    $replyContent .= "Utilisation de la commande !rmd";

                    $replyContent .= "\n!rmd [commande] => ajoute un rappel pour votre prochaine commande.";

                    $replyContent .= "\nExemples: !rmd daily ou encore !rmd hr";

                    $replyContent .= "\n\n!rmd [durée] [raison] => Ajoute un rappel dans [durée]";

                    $replyContent .= "\nExemple: !rmd 1h20m lance >mine => Le bot vous rappellera 'lance >mine' dans 1h20m";

                    $replyContent .= "\nFormat reconnu: 1y1d1h1m";


                    $replyContent .= "\n\n!rmd list => affiche la liste de tes rappels en attente";

                    $replyContent .= "\n!rmd remove ID => Supprime un rappel en attente";


                    $replyContent .= "\n\nLes rappels se font actuellement dans le chan #commandes-bot";

                    $replyContent .= " ```";

                    $message->channel->sendMessage(completementCon($replyContent));
                }
            }
        }
    }, [

        'description' => 'Stopping RealTimeTimer',

    ]);


    $discord->registerCommand('rmd', function ($message) {

        global $messageId, $waitingForMessage, $discord, $db;


        $guild = $discord->guilds->get('id', $message->guild_id);


        $converted = '';

        $args = array();

        if (strstr($message->content, ' '));

        $args = explode(' ', $message->content);

        if (count($args) > 2) {


            if ($args[1] == 'remove') {

                $timeNow = new DateTime("NOW");

                $rmdRequest = $db->query('SELECT * FROM `reminders` WHERE id=? AND user_id=?', (int) $args[2], $message->author->id)->fetchAll();

                if (!empty($rmdRequest)) {

                    $db->query("DELETE FROM reminders WHERE id = ?", (int) $args[2]);

                    $replyContent = "\nRappel supprimé";
                } else {

                    $replyContent = "\nRappel inconnu";
                }

                $message->channel->sendMessage(completementCon($replyContent));
            } else {

                $replyMessage = '';

                if (strstr($args[1], 'y') || strstr($args[1], 'd') || strstr($args[1], 'h') || strstr($args[1], 'm')) {


                    $timeArg = $args[1];

                    if (strpos($timeArg, 'y')) {

                        $converted .= "+ " . substr($timeArg, 0, strpos($timeArg, 'y')) . " years ";

                        $timeArg = substr($timeArg, strpos($timeArg, 'y') + 1, strlen($timeArg) - strpos($timeArg, 'y'));
                    }

                    if (strpos($timeArg, 'd')) {

                        $converted .= "+ " . substr($timeArg, 0, strpos($timeArg, 'd')) . " days ";

                        $timeArg = substr($timeArg, strpos($timeArg, 'd') + 1, strlen($timeArg) - strpos($timeArg, 'd'));
                    }

                    if (strpos($timeArg, 'h')) {

                        $converted .= "+ " . substr($timeArg, 0, strpos($timeArg, 'h')) . " hours ";

                        $timeArg = substr($timeArg, strpos($timeArg, 'h') + 1, strlen($timeArg) - strpos($timeArg, 'h'));
                    }

                    if (strpos($timeArg, 'm')) {

                        $converted .= "+ " . substr($timeArg, 0, strpos($timeArg, 'm')) . " minutes ";

                        $timeArg = substr($timeArg, strpos($timeArg, 'm') + 1, strlen($timeArg) - strpos($timeArg, 'm'));
                    }


                    $reason = "";

                    for ($cptArg = 2; $cptArg < count($args); $cptArg++)

                        $reason .= " " . $args[$cptArg];


                    $rmdDate = new DateTime("NOW");

                    $rmdDate->modify($converted);

                    $guild = $discord->guilds->get('id', $message->guild_id);


                    if ($guild != null) {

                        $member = $guild->members[$message->author->id];

                        $db->query('INSERT INTO reminders (user_id,date_reminder,reason) VALUES (?,?,?)', $message->author->id, $rmdDate->format("Y-m-d H:i:s"), $reason);

                        $replyMessage = " Ok, je te rappelerais dans **" . $args[1] . "** de : **" . $reason . "**";

                        $message->reply($replyMessage);
                    } else {

                        $message->reply('Non, pas ici, oust...');
                    }
                }
            }
        } elseif (count($args) == 2) {

            if ($args[1] == 'list' || $args[1] == "liste") {

                $replyContent = "```Markdown

Vos prochains rappels programmés";

                $rmdRequest = $db->query('SELECT * FROM `reminders` WHERE user_id=? ORDER BY `date_reminder` ASC LIMIT 10', $message->author->id)->fetchAll();

                for ($cptRmd = 0; $cptRmd < count($rmdRequest); $cptRmd++) {

                    $replyContent .= "\n" . $rmdRequest[$cptRmd]['id'] . " - " . $rmdRequest[$cptRmd]['date_reminder'] . " -> " . $rmdRequest[$cptRmd]['reason'];
                }

                $replyContent .= " ```";

                $message->channel->sendMessage(completementCon($replyContent));
            } else {

                $reason = "";

                switch ($args[1]) {
                    case 'hr':
                    case 'ho':
                    case 'hour':
                    case 'hourly':
                        $rmdDate = new DateTime("NOW");
                        $rmdDate->modify("+1 hours");
                        $reason = "Hourly";
                        $dureeTxt = "1 heure";
                        break;
                    case 'rep':
                        $rmdDate = new DateTime("NOW");
                        $rmdDate->modify("+24 hours");
                        $reason = "Rep";
                        $dureeTxt = "24 heure";
                        break;
                    case 'dr':
                    case 'drill':
                        $rmdDate = new DateTime("NOW");
                        $rmdDate->modify("+24 hours");
                        $reason = "Drill";
                        $dureeTxt = "24 heure";
                        break;
                    case 'da':
                    case 'dai':
                    case 'day':
                    case 'dail':
                    case 'daily':
                        $rmdDate = new DateTime("NOW");
                        $rmdDate->modify("+24 hours");
                        $reason = "Daily";
                        $dureeTxt = "24 heure";
                        break;
                    case 'gen':
                    case 'g':
                    case 'generator':
                        $rmdDate = new DateTime("NOW");
                        $rmdDate->modify("+4 hours");
                        $reason = "Générateur";
                        $dureeTxt = "4 heures";
                        break;
                    case 'cau':
                    case 'caul':
                    case 'cauld':
                    case 'cauldr':
                    case 'cauldro':
                    case 'cauldron':
                        $rmdDate = new DateTime("NOW");
                        $rmdDate->modify("+15 minutes");
                        $reason = "Cauldron";
                        $dureeTxt = "15 minutes";
                        break;
                    case 'vo':
                    case 'v':
                    case 'vote':
                        $rmdDate = new DateTime("NOW");
                        $rmdDate->modify("+12 hours");
                        $reason = "Vote";
                        $dureeTxt = "12 heures";
                        break;
                    default:

                        $message->reply("Demande inconnue...");

                        break;
                }

                if (!empty($reason)) {

                    $guild = $discord->guilds->get('id', $message->guild_id);


                    if ($guild != null) {

                        $member = $guild->members[$message->author->id];

                        $db->query('INSERT INTO reminders (user_id,date_reminder,reason) VALUES (?,?,?)', $message->author->id,  $rmdDate->format("Y-m-d H:i:s"), $reason);

                        $replyMessage = completementCon(" Reminder ajouté, je te rappelerais de faire ton **" . $reason . "** dans **" . $dureeTxt . "**");

                        $message->reply($replyMessage);
                    } else {

                        $message->reply('Non, pas ici, oust...');
                    }
                }
            }
        }
    }, [

        'description' => 'Stopping RealTimeTimer',

    ]);


    $discord->registerCommand('coucou', function ($message) {

        $whoami = "```Markdown

Tu veux voir ma...

                    ```";

        $message->channel->sendMessage($whoami);
    }, [

        'description' => 'Usefull command',

    ]);


    $discord->registerCommand('infos', function ($message) {

        if (in_array($message->author->id, array(125641223544373248))) {

            global $discord, $shardsinfos;


            $guildCount = $memberCount = 0;


            $details = false;

            $args = explode(' ', $message->content);

            if (count($args) > 1 && $args[1] == '-d') {

                $details = true;
            }

            $replyMess = '';

            foreach ($discord->guilds as $guild) {

                $guildCount++;

                if ($details)

                    $replyMess .= "\n" . $guild->name/*.' : '.count($guild->members).' members'*/;

                $memberCount += count($guild->members);
            }


            $replyMess .= "\nBot actif sur " . $guildCount . ' serveurs : ' . $memberCount . ' utilisateurs liés';


            $message->channel->sendMessage($replyMess);
        }
    }, [

        'description' => 'A little bit of information.',

    ]);


    $discord->registerCommand('kickUser', function ($message) {

        if (in_array($message->author->id, array(125641223544373248))) {

            global $discord, $shardsinfos;


            $args = explode(' ', $message->content);

            if (count($args) > 1) {

                foreach ($shardsinfos as $info) {

                    $guild = $discord->guilds->get('id', $info['guild_id']);


                    foreach ($guild->members as $member) {

                        if ($member->id == $args[1])

                            $guild->members->kick($member);
                    }
                }

                $replyMess = "Nettoyage effectué. Joueurs congédié";

                $message->channel->sendMessage($replyMess);
            }
        }
    }, [

        'description' => 'A little bit of information.',

    ]);

    /*
    $discord->registerCommand('t', function ($message) {

        global $discord;

        global $guildListInt;

        if (isset($guildListInt[$message->guild_id])) {

            $guild = $discord->guilds->get('id', 554645551501541377);

            if (in_array($message->author->id, array(125641223544373248)) || checkRole($guild->members->get('id', $message->author->id), "Premium"))

                $replyContent = getTimers();

            else

                $replyContent = "Role Premium requis!";

        } else {

            $replyContent = getTimers();

        }


        $message->channel->sendMessage($replyContent);

    }, [

        'description' => 'A little bit of information.',

    ]);
*/

    function checkRole($member, $roleToCheck = "Premium")

    {

        $returnValue = false;

        if (count($member->roles) > 0) {

            foreach ($member->roles as $memberRole) {


                if ($memberRole['name'] == $roleToCheck)

                    $returnValue = true;
            }
        }

        return $returnValue;
    }


    $discord->registerCommand('addVIP', function ($message) {

        global $guildListInt;

        if (isset($guildListInt[$message->guild_id])) {

            global $discord, $shardsinfos;


            $guild = $discord->guilds->get('id', 554645551501541377);

            if (in_array($message->author->id, array(125641223544373248)) || checkRole($guild->members->get('id', $message->author->id), "Modo")) {

                $user_id = 0;

                $args = array();

                if (strstr($message->content, ' '));

                $args = explode(' ', $message->content);

                if (count($args) == 2) {

                    $replyMessage = '';

                    if (strstr($args[1], '<@')) {

                        $user_id = str_replace(array('<@', '>'), '', $args[1]);
                    }

                    if (!empty($user_id) && !empty($guild->members[$user_id])) {

                        $member = $guild->members[$user_id];

                        $role = $guild->roles->get("name", "VIP");


                        $checkResult = checkRole($member, "VIP");

                        if (!$checkResult) {

                            $member->addRole($role);

                            $guild->members->save($member)->then(function () {

                                /**Success**/
                            }, function ($e) {

                                print_r($e->getMessage());
                            });
                        }


                        foreach ($shardsinfos as $info) {

                            $guild = $discord->guilds->get('id', $info['guild_id']);

                            $role = $guild->roles->get("name", "VIP");

                            if (!empty($guild->members[$user_id])) {

                                $member = $guild->members[$user_id];

                                $checkResult = checkRole($member, "VIP");

                                if (!$checkResult) {

                                    $member->addRole($role);

                                    $guild->members->save($member)->then(function () {

                                        /**Success**/
                                    }, function ($e) {

                                        print_r($e->getMessage());
                                    });
                                }
                            }
                        }

                        $message->channel->sendMessage(completementCon("VIP ajouté"));
                    }
                }
            }
        }
    }, [

        'description' => 'A little bit of information.',

    ]);


    $discord->registerCommand('removeVIP', function ($message) {

        global $guildListInt;

        if (isset($guildListInt[$message->guild_id])) {

            global $discord, $shardsinfos;


            $guild = $discord->guilds->get('id', 554645551501541377);

            if (in_array($message->author->id, array(125641223544373248)) || checkRole($guild->members->get('id', $message->author->id), "Modo")) {

                $user_id = 0;

                $args = array();

                if (strstr($message->content, ' '));

                $args = explode(' ', $message->content);

                if (count($args) == 2) {

                    $replyMessage = '';

                    if (strstr($args[1], '<@')) {

                        $user_id = str_replace(array('<@', '>'), '', $args[1]);
                    }

                    if (!empty($user_id) && !empty($guild->members[$user_id])) {

                        $member = $guild->members[$user_id];

                        $role = $guild->roles->get("name", "VIP");


                        $checkResult = checkRole($member, "VIP");

                        if ($checkResult) {

                            $member->removeRole($role);

                            $guild->members->save($member)->then(function () {

                                /**Success**/
                            }, function ($e) {

                                print_r($e->getMessage());
                            });
                        }


                        foreach ($shardsinfos as $info) {

                            $guild = $discord->guilds->get('id', $info['guild_id']);

                            $role = $guild->roles->get("name", "VIP");

                            if (!empty($guild->members[$user_id])) {

                                $member = $guild->members[$user_id];

                                $checkResult = checkRole($member, "VIP");

                                if ($checkResult) {

                                    $member->removeRole($role);

                                    $guild->members->save($member)->then(function () {

                                        /**Success**/
                                    }, function ($e) {

                                        print_r($e->getMessage());
                                    });
                                }
                            }
                        }

                        $message->channel->sendMessage(completementCon("VIP Removed"));
                    }
                }
            }
        }
    }, [

        'description' => 'A little bit of information.',

    ]);


    //Run that shit dawg

    $discord->run();
} catch (\Exception $e) {

    echo $e->getMessage();
}
