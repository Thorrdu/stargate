<?php
//HELP EN
return [
    'usage' => 'Usage',
    'mainHelp' => 'Run !help command to get more information about a specific command.',
    'ban' => [
        'description' => 'Ban/Unban a player from the bot.',
        'usage' => '!ban @mention'
    ],
    'reminder' => [
        'description' => 'Allow to create, list and remove reminders wich will be received in dm.',
        'usage' => "!reminder [time] [reason]\nreminder list\n!reminder 1h20m5s go to sleep\n!reminder remove [id]"
    ],
    'build' => [
        'description' => "List available buildings, Show building details or build/upgrade some building.",
        'usage' => "!build\n!build [id/slug]\n!build [id/slug] confirm\n!build [id/slug] remove\n!build cancel"
    ],
    'research' => [
        'description' => "List available technologies, Show research details start/upgrade some research.",
        'usage' => "!research\n!research [id/slug]\n!research [id/slug] confirm"
    ],
    'colony' => [
        'description' => 'Display some esential information about your colony (Resources, Buildings, Production, ... ). Also allows to switch between your colonies.',
        'usage' => "!colony\n!colony list\n!colony switch [number]\n!colony remove [number]\n!colony rename [new name]"
    ],
    'craft' => [
        'description' => "List available crafts such as probes to spy your oponants, transports to move resources through the gate, ...",
        'usage' => "!craft list\n!craft queue\n!craft [id/slug] [quantity]"
    ],
    'defence' => [
        'description' => "List available defences to build and protect your colonies",
        'usage' => "!defence list\n!defence queue\n!defence [id/slug] [quantity]"
    ],
    'galaxy' => [
        'description' => "Display a view of the current galaxy"
                        ."\nYour vision range depends on your Communication technology."
                        ."\nVision: 2^Lvl visible systems around you."
                        ."\nExamples: \nLvl 0: You only see your home system."
                        ."\nLvl 3: You can see up to 8 systems around you."
                        ."\nLvl 8: Vision to all galaxies",
        'usage' => '!galaxy'
    ],
    'alliance' => [
        'description' => "Allows you to create or manage your alliance.",
        'usage' => "!alliance list\n".
                    "!alliance create [Tag] [Name]\n".
                    "!alliance set internal_description [Description]\n".
                    "!alliance set external_description [Description]\n".
                    "!alliance set leader [@mention]\n".
                    "!alliance set recruitement [on/off]\n".
                    "!alliance role list\n".
                    "!alliance role [role] set [parameter] [value/on/off]\n".
                    "!alliance [invite/promote/demote/kick] [@mention]\n".
                    "!alliance leave\n".
                    "!alliance disband\n".
                    "!alliance upgrade\n"
    ],
    'stargate' => [
        'description' => "Access to the Stargate on your planet\nStart exploration missions on distant planets to obtain information, resources or more, Spy people or trade resources with other players",
        'usage' => "!stargate explore [coordinates]\n".
                    "!stargate colonize [coordinates]\n".
                    "!stargate move [colonyNumber] [Res1] [Qty1]\n".
                    "!stargate move [coordinates] [Res1] [Qty1]\n".
                    "!stargate spy [coordinates]\n".
                    "!stargate attack [coordinates] military [Qty] [Unit1] [Qty1]\n".
                    "!stargate bury\n"
    ],
    'shipyard' => [
        'description' => "Allows you to build spaceships or making new custom models".
                         "\n\nTo learn more about custom spaceship models use `!shipyard create`",
        'usage' => "!shipyard [Slug] [Quantity]\n".
                    "!shipyard queue\n".
                    "!shipyard parts\n".
                    "!shipyard create [blueprint] [...Components]\n".
                    "!shipyard rename [oldSlug] [New name]\n".
                    "!shipyard remove [Slug]\n"
    ],
    'fleet' => [
        'description' => "Centre de contrôle des flottes\nIndique les flottes en cours et vous permet de donner des ordres de mission à vos vaisseaux à quai",
        'usage' =>  "!fleet \n".
                    "**order** (`!fleet order [FleetID] return`)\n".
                    //"**explore** (`!fleet explore [coordinates]`)\n".
                    //"**colonize** (`!fleet colonize [coordinates]`)\n".
                    "**base** (`!fleet base [colonyNumber] [Ship] [Qty] [Res1] [Qty]`)\n".
                    "**transport** (`!fleet transport [coordinates] [Ship] [Qty] [Res] [Qty]`)\n".
                    "**spy** (`!fleet spy [coordinates]`) \n".
                    "**attack** (`!fleet attack [coordinates] [Ship] [Qty]`)\n".
                    "**scavenge** (`!fleet scavenge [Scavengers] [Qty]`)\n".
                    "**history** (`!fleet history`)"
    ],
    'infos' => [
        'description' => 'Display information on Stargate Bot such as Author, support server invite, ....',
        'usage' => '!infos'
    ],
    'invite' => [
        'description' => "Display a link to invite Stargate on your server.",
        'usage' => '!invite'
    ],
    'lang' => [
        'description' => 'Change language.',
        'usage' => '!lang [fr/en]'
    ],
    'captcha' => [
        'description' => 'Allow to resend a captcha link in case you need it.',
        'usage' => '!captcha'
    ],
    'ping' => [
        'description' => 'Display Stargate latency.',
        'usage' => '!ping'
    ],
    'profile' => [
        'description' => "Display information about your profile such as lang, vote number, ..."
                        ."\nAlso allows to manage your notification at the end of building/research",
        'usage' => "!profile\n!profile notification [on/off]\n!profile vacation"
    ],
    'premium' => [
        'description' => "If you want to support the bot, you can buy a premium through this link: **TO BE DEFINED**"
                        ."\nYou can also use or give a premium once you bought it.\n\nPremium advantages:".
                        "=> +25% basic resources production\n".
                        "=> Possibility to rename your colonies\n".
                        "=> Access to the `!empire` command",
        'usage' => "!premium\n!premium use\n!premium give @mention"
    ],
    'empire' => [
        'description' => "Allow you to see an overview of your colonies and to claim resources / check building/research/craft/defence ending with one command",
        'usage' => "!empire\n!empire fleet\n!empire artifacts"
    ],
    'start' => [
        'description' => "The first commande to start your Stargate adventure. Use it to create your player profile",
        'usage' => '!start'
    ],
    'top' => [
        'description' => 'Shows the best player for each category.',
        'usage' => "!top [general/building/research/craft/defence/military]\n!top [general/building/research/craft/defence/military] alliance"
    ],
    'trade'=> [
        'description' => "List all your active trades.\nDisplay a specific trade details with `!trade [id]´\nClose a trade before the end with `!trade [id] close´\nAsk for a time extention with `!trade [id] extend´",
        'usage' =>  "!trade list\n".
                    "!trade [ID]\n".
                    "!trade [ID] close\n".
                    "!trade [ID] extend\n"
    ],
    'uptime' => [
        'description' => "Display the Stargate uptime.",
        'usage' => '!uptime'
    ],
    'vote' => [
        'description' => "If you appreciate Stargate, your can vote for him with the link behind this command every 12h.",
        'usage' => "!vote\n!vote use"
    ],
    'daily' => [
        'description' => "Daily reward",
        'usage' => '!daily'
    ],
    'hourly' => [
        'description' => "Hourly reward",
        'usage' => '!hourly'
    ],
];
