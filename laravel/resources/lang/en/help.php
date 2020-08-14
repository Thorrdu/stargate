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
        'usage' => "!build\n!build [id/slug]\n!build [id/slug] confirm"
    ],
    'research' => [
        'description' => "List available technologies, Show research details start/upgrade some research.",
        'usage' => "!research\n!research [id/slug]\n!research [id/slug] confirm"
    ],
    'colony' => [
        'description' => 'Display some esential information about your colony (Resources, Buildings, Production, ... ). Also allows to switch between your colonies.',
        'usage' => "!colony\n!colony switch [number]"
    ],
    'craft' => [
        'description' => "List available crafts such as probes to spy your oponants, transports to move resources through the gate, ...",
        'usage' => "!craft list\n!craft queue\n!craft [id/slug] [quantity]"
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
    'stargate' => [
        'description' => "Access to the Stargate on your planet\nStart exploration missions on distant planets to obtain information, resources or more, Spy people or trade resources with other players",
        'usage' => "!stargate\n!stargate explore [coordinate]\n!stargate spy [coordinate]\n!stargate trade [coordinate] [Res1] [Qty1]\n!stargate colonize [coordinate]"
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
    'ping' => [
        'description' => 'Display Stargate latency.',
        'usage' => '!ping'
    ],
    'profile' => [
        'description' => "Display information about your profile such as lang, vote number, colonies list, ..."
                        ."\nAlso allows to manage your notification at the end of building/research",
        'usage' => "!profile\n!profile notification [on/off]"
    ],
    'start' => [
        'description' => "The first commande to start your Stargate adventure. Use it to create your player profile",
        'usage' => '!start'
    ],
    'top' => [
        'description' => 'Shows the best player for each category.',
        'usage' => "!top [general/building/research/military/defence]"
    ],
    'uptime' => [
        'description' => "Display the Stargate uptime.",
        'usage' => '!uptime'
    ],
    'vote' => [
        'description' => "If you appreciate Stargate, your can vote for him with the link behind this command every 12h.",
        'usage' => '!vote'
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