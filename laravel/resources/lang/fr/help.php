<?php
//HELP FR
return [
    'usage' => 'Exemple(s)',
    'mainHelp' => "Utilisez `:prefixhelp [commande]` pour obtenir plus d'informations sur une commande.",
    'tutorial' => [
        'description' => "Quelques pistes pour les nouveaux joueurs",
        'usage' => ":prefixtutorial"
    ],
    'ban' => [
        'description' => 'Ban/Unban un joueur du bot.',
        'usage' => ':prefixban @mention'
    ],
    'reminder' => [
        'description' => "Permet de créer, lister et supprimer des rappels envoyés en dm.",
        'usage' => ":prefixreminder [temps] [motif]\nreminder list\n:prefixreminder 1h20m5s va dormir\n:prefixreminder remove [id]"
    ],
    'build' => [
        'description' => "Permet de lister les bâtiments disponible, d'afficher le détails d'un bâtiment ou encore construire/upgrade un bâtiment."
                        ."\nAnnuler une construction fait perdre 25% des ressources investies.",
        'usage' => ":prefixbuild\n:prefixbuild [id/slug]\n:prefixbuild [id/slug] confirm\n:prefixbuild [id/slug] remove\n:prefixbuild cancel"
    ],
    'research' => [
        'description' => "Permet de lister les technologies disponible, d'afficher le détails d'une technologie ou encore rechercher/upgrade une technologie."
                        ."\nAnnuler une recherche fait perdre 25% des ressources investies.",
        'usage' => ":prefixresearch\n:prefixresearch [id/slug]\n:prefixresearch [id/slug] confirm\n:prefixresearch cancel"
    ],
    'colony' => [
        'description' => 'Affiche les informations essentielles sur votre colonie (Ressources, Bâtiments, Production, ... ) et permet de changer de colonie.',
        'usage' => ":prefixcolony\n:prefixcolony list\n:prefixcolony switch [numéro]\n:prefixcolony remove [numéro]\n:prefixcolony rename [nouveau nom]"
    ],
    'craft' => [
        'description' => "Construit des appareils tels que des sondes permettant d'espionner les autres joueurs ou des transporteur pour acheminer vos ressources à travers la porte",
        'usage' => ":prefixcraft list\n:prefixcraft queue\n:prefixcraft [id/slug] [quantité]"
    ],
    'defence' => [
        'description' => "Permet de lister et construire des défenses pour protéger vos colonies",
        'usage' => ":prefixdefence list\n:prefixdefence queue\n:prefixdefence [id/slug] [quantité]"
    ],
    'prefix' => [
        'description' => "Permet de changer le prefix du bot sur ce serveur (Réservé aux administrateurs du serveur)",
        'usage' => ":prefixprefix\n:prefix [new prefix]"
    ],
    'channel' => [
        'description' => "Permet d'indiquer au bot d'ignorer ce channel",
        'usage' => ":prefixchannel ignore on/off"
    ],
    'galaxy' => [
        'description' => "Affiche une vue de la galaxie"
                        ."\nVotre zone de vision dépend de la technologie Informatique et Communication."
                        ."\nVision: 2^Lvl systèmes visibles autour de vous."
                        ."\nExemples: \nLvl 0: vous voyez votre système solaire."
                        ."\nLvl 3: Vous voyez 8 systèmes autour du votre"
                        ."\nLvl 8: Accès à la vision des autres galaxies",
        'usage' => ':prefixgalaxy'
    ],
    'alliance' => [
        'description' => "Vous permet de créer ou gérer votre alliance.",
        'usage' => ":prefixalliance list\n".
                    ":prefixalliance create [Tag] [Name]\n".
                    ":prefixalliance set internal_description [Description]\n".
                    ":prefixalliance set external_description [Description]\n".
                    ":prefixalliance set leader [@mention]\n".
                    ":prefixalliance set recruitement [on/off]\n".
                    ":prefixalliance role list\n".
                    ":prefixalliance role [role] set [paramètre] [valeur/on/off]\n".
                    ":prefixalliance [invite/promote/demote/kick] [@mention]\n".
                    ":prefixalliance leave\n".
                    ":prefixalliance disband\n".
                    ":prefixalliance upgrade\n"
    ],
    'stargate' => [
        'description' => "Accès à la porte des étoiles de votre colonie\nPermet de partir explorer d'autres planètes afin d'obtenir informations et ressources, d'espionner ou commercer avec les autres joueur voir de les attaquer".
                        "**Lvl 5 - Centre de recherche** est requis pour activer la porte vers d'autres planètes..\n".
                        "Cependant, au Lvl 4, les autres joueurs pourront se connecter à votre porte",
        'usage' => ":prefixstargate explore [coordonées]\n".
                    ":prefixstargate colonize [coordonées]\n".
                    ":prefixstargate move [NuméroDeColonie] [Res1] [Qté1]\n".
                    ":prefixstargate move [coordonées] [Res1] [Qté1]\n".
                    ":prefixstargate spy [coordonées]\n".
                    ":prefixstargate attack [coordonées] military [Qté] [Unit1] [Qté1]\n".
                    ":prefixstargate bury"
    ],
    'shipyard' => [
        'description' => "Permet de construire des vaisseaux spatiaux ou d'établir de nouveaux plans personnalisés".
                        "\n\nPour en savoir plus sur la création d'un modèle personnalisé, consultez `:prefixshipyard create`",
        'usage' => ":prefixshipyard [Slug] [Quantité]\n".
                    ":prefixshipyard queue\n".
                    ":prefixshipyard parts\n".
                    ":prefixshipyard create [blueprint] [...Composants]\n".
                    ":prefixshipyard rename [oldSlug] [Nouveau nom]\n".
                    ":prefixshipyard remove [Slug]\n"
    ],
    'fleet' => [
        'description' => "Centre de contrôle des flottes\nIndique les flottes en cours et vous permet de donner des ordres de mission à vos vaisseaux à quai",
        'usage' =>  ":prefixfleet \n".
                    "**order** (`:prefixfleet order [id] return`)\n".
                    //"**explore** (`:prefixfleet explore [coordonées]`)\n".
                    //"**colonize** (`:prefixfleet colonize [coordonées]`)\n".
                    "**base** (`:prefixfleet base [NuméroDeColonie] [Vaisseaux] [Nombre] [Ressource] [Qté]`)\n".
                    "**transport** (`:prefixfleet transport [coordonées] [Vaisseaux] [Nombre] [Ressource] [Qté]`)\n".
                    "**spy** (`:prefixfleet spy [coordonées]`)\n".
                    "**attack** (`:prefixfleet attack [coordonées] [Vaisseaux] [Qté]`)\n".
                    "**scavenge** (`:prefixfleet scavenge [Recycleurs] [Qté]`)\n".
                    "**history** (`:prefixfleet history`)"
    ],
    'infos' => [
        'description' => 'Affiche les informations sur Stargate Bot.',
        'usage' => ':prefixinfos'
    ],
    'invite' => [
        'description' => "Affiche le lien permettant d'inviter Stargate sur votre serveur.",
        'usage' => ':prefixinvite'
    ],
    'lang' => [
        'description' => 'Donne la possibilité de changer de langue.',
        'usage' => ':prefixlang [fr/en]'
    ],
    'captcha' => [
        'description' => 'Permet de renvoyer le lien du captcha en cas de besoin.',
        'usage' => ':prefixcaptcha'
    ],
    'ping' => [
        'description' => 'Indique la latence de Stargate Bot.',
        'usage' => ':prefixping'
    ],
    'profile' => [
        'description' => "Affiche les informations de votre profile tel que vote langue, nombre de vote,..."
                        ."\nPermet également de configurer la réception de notifications lors de la fin de construction/recherche ou quand votre vote est de nouveau disponbible.".
                        "\nCette commande permet également d'activer ou désactiver le mode vacance.",
        'usage' => ":prefixprofile\n:prefixprofile notification [on/off]\n:prefixprofile vacation"
    ],
    'premium' => [
        'description' => "Si vous désirez apporter votre soutien au bot, vous pouvez acheter un premium via ce lien: **[Utip](https://utip.io/thorrdu)** (Pour payer par Paysafe Card ou Paypal, contactez Thorrdu en DM)\n".
                        "Prix: 5 Euros = 1 Mois / 50 Euros = 1 an.\n".
                        "\nUne fois acheté, vous pouvez également utiliser ou offrir un premium\n\nAvantage du premium:\n".
                        "=> +25% de production des ressources basiques\n".
                        "=> Possibilité de renommer vos colonies\n".
                        "=> Accès à la commande `:prefixempire`",
        'usage' => ":prefixpremium\n:prefixpremium use\n:prefixpremium give @mention"
    ],
    'empire' => [
        'description' => "Vous permet d'afficher une vue d'ensemble de vos colonies, réclamer leur ressources / vérifier la fin de vos building/research/craft/defence en une commande",
        'usage' => ":prefixempire\n:prefixempire activities\n:prefixempire production\n:prefixempire fleet\n:prefixempire artifacts"
    ],
    'start' => [
        'description' => "Créer votre profile Stargate afin de commencer votre aventure.",
        'usage' => ':prefixstart'
    ],
    'top' => [
        'description' => "Indique les meilleurs joueur par catégories.\n1 point = 1k ressources dépensées",
        'usage' =>  ":prefixtop [general/building/research/craft/defence/military]\n:prefixtop [general/building/research/craft/defence/military] alliance"
    ],
    'trade'=> [
        'description' => "Liste les trades actifs.\nAfficher le détail d'un trade via `:prefixtrade [id]`\nClôturer un trade en cours avant la date de fin avec `:prefixtrade [id] close`\nDemander le prolongemnet d'un trade avec `:prefixtrade [id] extend`".
                        "\nInviter un joueur à un pacte commercial avec `:prefixtrade pact <mention>` ou annulez-en un avec `:prefixtrade pact <mention> cancel` ",
        'usage' =>  ":prefixtrade list\n".
                    ":prefixtrade ratio\n".
                    ":prefixtrade [ID]\n".
                    ":prefixtrade [ID] close\n".
                    ":prefixtrade [ID] extend\n".
                    ":prefixtrade pact list\n".
                    ":prefixtrade pact [mention/ID]\n".
                    ":prefixtrade pact [mention/ID] cancel\n"
    ],
    'uptime' => [
        'description' => "Indique la durée depuis laquelle le bot est en ligne.",
        'usage' => ':prefixuptime'
    ],
    'vote' => [
        'description' => "Si vous appréciez Stargate, vous pouvez voter pour ce bot avec le lien derrière cette commande.",
        'usage' => ":prefixvote\n:prefixvote use"
    ],
    'daily' => [
        'description' => "Récompense quotidienne",
        'usage' => ':prefixdaily'
    ],
    'hourly' => [
        'description' => "Récompense horaire",
        'usage' => ':prefixhourly'
    ],
];
