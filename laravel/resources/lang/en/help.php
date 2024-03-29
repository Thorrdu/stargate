<?php
//HELP EN
return [
    'usage' => 'Usage',
    'mainHelp' => 'Run `:prefixhelp` command to get more information about a command.',
    'tutorial' => [
        'description' => "Some clues for new players",
        'usage' => ":prefixtutorial"
    ],
    'ban' => [
        'description' => 'Ban/Unban a player from the bot.',
        'usage' => ':prefixban @mention'
    ],
    'reminder' => [
        'description' => "Allow to create, list and remove reminders wich will be received in dm.It's also possible to consult the history of received dm.\n",
        'usage' => ":prefixreminder [time] [reason]\nreminder list\n:prefixreminder 1h20m5s go to sleep\n:prefixreminder history\n:prefixreminder history [id]\n:prefixreminder remove [id]"
    ],
    'build' => [
        'description' => "List available buildings, Show building details or build/upgrade some building."
                        ."\nCanceling a building makes you loose 25% of invested resources.",
        'usage' => ":prefixbuild\n:prefixbuild [id/slug]\n:prefixbuild [id/slug] confirm\n:prefixbuild queue\n:prefixbuild [id/slug] remove\n:prefixbuild cancel"
    ],
    'research' => [
        'description' => "List available technologies, Show research details start/upgrade some research."
                        ."\nCanceling a research makes you loose 25% of invested resources.",
        'usage' => ":prefixresearch\n:prefixresearch [id/slug]\n:prefixresearch [id/slug] confirm\n:prefixresearch cancel"
    ],
    'colony' => [
        'description' => 'Display some esential information about your colony (Resources, Buildings, Production, ... ). Also allows to switch between your colonies.',
        'usage' => ":prefixcolony\n:prefixcolony list\n:prefixcolony switch [number]\n:prefixcolony remove [number]\n:prefixcolony reroll\n:prefixcolony rename [new name]"
    ],
    'craft' => [
        'description' => "List available crafts such as probes to spy your oponants, transports to move resources through the gate, ...",
        'usage' => ":prefixcraft list\n:prefixcraft queue\n:prefixcraft [id/slug] [quantity]"
    ],
    'defence' => [
        'description' => "List available defences to build and protect your colonies",
        'usage' => ":prefixdefence list\n:prefixdefence queue\n:prefixdefence [id/slug] [quantity]"
    ],
    'prefix' => [
        'description' => "Change the bot's prefix for this server. (Restricted to server admins)",
        'usage' => ":prefixprefix\n:prefix [new prefix]"
    ],
    'channel' => [
        'description' => "The bot will ignore a channel. (Restricted to server admins)",
        'usage' => ":prefixchannel ignore [on/off]"
    ],
    'galaxy' => [
        'description' => "Display a view of the current galaxy"
                        ."\nYour vision range depends on your Communication technology."
                        ."\nVision: 2^Lvl visible systems around you."
                        ."\nExamples: \nLvl 0: You only see your home system."
                        ."\nLvl 3: You can see up to 8 systems around you."
                        ."\nLvl 8: Vision to all galaxies",
        'usage' => ':prefixgalaxy'
    ],
    'news' => [
        'description' => "The BFM Galaxic News keeps abreast of special activities taking place in the universe.\n".
                        "The editorial line contains information such as the arrival of new empires or the follow-up of escalations between empires.\n".
                        "Once long-range scanners are restored, tracking of pirate fleets can also be done.",
        'usage' => ':prefixnews'
    ],
    'alliance' => [
        'description' => "Allows you to create or manage your alliance.",
        'usage' => ":prefixalliance list\n".
                    ":prefixalliance create [Tag] [Name]\n".
                    ":prefixalliance set internal_description [Description]\n".
                    ":prefixalliance set external_description [Description]\n".
                    ":prefixalliance set leader [@mention]\n".
                    ":prefixalliance set recruitement [on/off]\n".
                    ":prefixalliance role list\n".
                    ":prefixalliance role [role] set [parameter] [value/on/off]\n".
                    ":prefixalliance [invite/promote/demote/kick] [@mention]\n".
                    ":prefixalliance leave\n".
                    ":prefixalliance disband\n".
                    ":prefixalliance upgrade\n"
    ],
    'flex' => [
        'description' => "Allows you to show to flex your resources to other players.",
        'usage' => ":prefixflex [resource] [quantity/all]"
    ],
    'dakara' => [
        'description' => "With the momentum garnered by Dakara's superweapon traveling through subspace and spreading to the orbit of the destination planet, you can cause instant damage to the entire defensive system of an opposing colony and this, even if his stargate is buried.".
                        "\n\nThe damage caused is 10% of defenses and 1 hour of military production per level of difference between yours and your opponent's. (Maximum destruction rate: 30%)".
                        "\nThe base range of your superweapon is 2 systems and doubles at each level (2 systems ^ level). Every 128 systems range you gain access to an additional galaxy.".

                        "\n\nOne use of Dakara's superweapon counts as one attack made through the door in your daily attack cycle.",
        'usage' => ':prefixdakara [Coordinates]'
    ],
    'stargate' => [
        'description' => "Access to the Stargate on your planet\nStart exploration missions on distant planets to obtain information, resources or more, Spy people or trade resources with other players".
                        "\n**Lvl 5 - Research Laboratory** required to use the gate to contact other planets.\n".
                        "However, at Lvl 4 you'll be able to receive gate dialing",
        'usage' => ":prefixstargate explore [coordinates]\n".
                    ":prefixstargate colonize [coordinates]\n".
                    ":prefixstargate move [colonyNumber/coordinates] [Res1] [Qty1]\n".
                    ":prefixstargate trade [coordinates] [Res1] [Qty1]\n".
                    ":prefixstargate spy [coordinates]\n".
                    ":prefixstargate attack [coordinates] military [Qty] [Unit1] [Qty1]\n".
                    ":prefixstargate bury\n"
    ],
    'shipyard' => [
        'description' => "Allows you to build spaceships or making new custom models".
                         "\n\nTo learn more about custom spaceship models use `:prefixshipyard create`",
        'usage' => ":prefixshipyard [Slug] [Quantity]\n".
                    ":prefixshipyard [Slug] recycle [Quantity]\n".
                    ":prefixshipyard queue\n".
                    ":prefixshipyard parts\n".
                    ":prefixshipyard create [blueprint] [...Components]\n".
                    ":prefixshipyard rename [oldSlug] [New name]\n".
                    ":prefixshipyard remove [Slug]\n"
    ],
    'fleet' => [
        'description' => "Fleet Control Center \nIndicates current fleets and allows you to issue mission orders to your ships docked.\n\n".
                        "Example to transport 100 iron and 50 gold to the colony located in [1;1;1] with 3 ships named 'MyShip' (slug: myship):".
                        "\n`!fleet transport 1;1;1 myship 3 iron 100 gold 50`".
                        "\n\nOptional parameters:".
                        "\nspeed [between 10 and 100] => reduces the speed and consumption of your fleet to the desired percentage.".
                        "\nboost => consumes 0.5 E2PZ per ship and grants +10% shield and +20% speed bonuses",
        'usage' =>  ":prefixfleet \n".
                    "**order** (`:prefixfleet order [FleetID] return`)\n".
                    //"**explore** (`:prefixfleet explore [coordinates]`)\n".
                    //"**colonize** (`:prefixfleet colonize [coordinates]`)\n".
                    "**base** (`:prefixfleet base [colonyNumber] [Ship] [Qty] [Res1] [Qty]`)\n".
                    "**transport** (`:prefixfleet transport [coordinates] [Ship] [Qty] [Res] [Qty]`)\n".
                    "**spy** (`:prefixfleet spy [coordinates]`) \n".
                    "**attack** (`:prefixfleet attack [coordinates] [Ship] [Qty]`)\n".
                    "**scavenge** (`:prefixfleet scavenge [coordinates] [Scavengers] [Qty]`)\n".
                    "**history** (`:prefixfleet history`)"
    ],
    'infos' => [
        'description' => 'Display information on Stargate Bot such as Author, support server invite, ....',
        'usage' => ':prefixinfos'
    ],
    'invite' => [
        'description' => "Display a link to invite Stargate on your server.",
        'usage' => ':prefixinvite'
    ],
    'lang' => [
        'description' => 'Change language.',
        'usage' => ':prefixlang [fr/en]'
    ],
    'captcha' => [
        'description' => 'Allow to resend a captcha link in case you need it.',
        'usage' => ':prefixcaptcha'
    ],
    'ping' => [
        'description' => 'Display Stargate latency.',
        'usage' => ':prefixping'
    ],
    'profile' => [
        'description' => "Display information about your profile such as lang, vote number, ..."
                        ."\nAlso allows to manage your notification at the end of building/research or when your vote is available.".
                        "\nIf you wish to, you can hide your coordinates when displaying `!colony`.".
                        "\nThis command also allow to activate or disable the vacation mode.",
        'usage' => ":prefixprofile\n:prefixprofile notification [on/off]\n:prefixprofile hide [on/off]\n:prefixprofile vacation"
    ],
    'premium' => [
        'description' => "If you want to support the bot, you can buy a premium through this link: **[Utip](https://utip.io/thorrdu)** (For payement with Paypal or Paysafecard, contact Thorrdu in DM)\n".
                        "Price: 5 Euros = 1 Month / 5 Premium bought = 1 Premium offered.\n".
                        "\nYou can also use or give a premium once you bought it.\n\nPremium advantages:\n".
                        "=> +30% production\n".
                        "=> -20% building/research time\n".
                        "=> Possibility to rename your colonies\n".
                        "=> Access to the `:prefixempire` command\n".
                        "=> Building construction queue\n",
        'usage' => ":prefixpremium\n:prefixpremium use\n:prefixpremium give @mention\n:prefixpremium give @mention [quantité]"
    ],
    'empire' => [
        'description' => "Allow you to see an overview of your colonies and claiming resources / check building/research/craft/defence ending with one command",
        'usage' => ":prefixempire\n:prefixempire activities\n:prefixempire production\n:prefixempire buildings\n:prefixempire fleet\n:prefixempire artifacts"
    ],
    'start' => [
        'description' => "The first commande to start your Stargate adventure. Use it to create your player profile",
        'usage' => ':prefixstart'
    ],
    'top' => [
        'description' => 'Shows the best player for each category.',
        'usage' => ":prefixtop [general/building/research/craft/military]\n:prefixtop [general/building/research/craft/military] alliance"// /defence
    ],
    'trade'=> [
        'description' => "List all your active trades.\nDisplay a specific trade details with `:prefixtrade [id]`\nClose a trade before the end with `:prefixtrade [id] close`".
                        "\nInvite a player to a trade pact with `:prefixtrade pact <mention>` or cancel an existing on with `:prefixtrade pact <mention> cancel` ",
        'usage' =>  ":prefixtrade list\n".
                    ":prefixtrade ratio\n".
                    ":prefixtrade [ID]\n".
                    ":prefixtrade [ID] close\n".
                    ":prefixtrade pact list\n".
                    ":prefixtrade pact [mention/ID]\n".
                    ":prefixtrade pact [mention/ID] cancel\n"
    ],
    'uptime' => [
        'description' => "Display the Stargate uptime.",
        'usage' => ':prefixuptime'
    ],
    'vote' => [
        'description' => "If you appreciate Stargate, your can vote for him with the link behind this command every 12h.",
        'usage' => ":prefixvote\n:prefixvote use"
    ],
    'daily' => [
        'description' => "Daily reward",
        'usage' => ':prefixdaily'
    ],
    'hourly' => [
        'description' => "Hourly reward",
        'usage' => ':prefixhourly'
    ],
];
