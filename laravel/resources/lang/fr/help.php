<?php
//HELP FR
return [
    'usage' => 'Exemple(s)',
    'mainHelp' => "Utilisez `!help [commande]` pour obtenir des informations supplémentaire comme un exemple d'utilisation.",

    'ban' => [
        'description' => 'Ban/Unban un joueur du bot.',
        'usage' => '!ban @mention'
    ],
    'reminder' => [
        'description' => "Permet de créer, lister et supprimer des rappels envoyés en dm.",
        'usage' => "!reminder [temps] [motif]\nreminder list\n!reminder 1h20m5s va dormir\n!reminder remove [id]"
    ],
    'build' => [
        'description' => "Permet de lister les bâtiments disponible, d'afficher le détails d'un bâtiment ou encore construire/upgrade un bâtiment.",
        'usage' => "!build\n!build [id/slug]\n!build [id/slug] confirm\n!build [id/slug] remove\n!build cancel"
    ],
    'research' => [
        'description' => "Permet de lister les technologies disponible, d'afficher le détails d'une technologie ou encore rechercher/upgrade une technologie.",
        'usage' => "!research\n!research [id/slug]\n!research [id/slug] confirm"
    ],
    'colony' => [
        'description' => 'Affiche les informations essentielles sur votre colonie (Ressources, Bâtiments, Production, ... ) et permet de changer de colonie.',
        'usage' => "!colony\n!colony list\n!colony switch [numéro]\n!colony remove [numéro]\n!colony rename [nouveau nom]"
    ],
    'craft' => [
        'description' => "Construit des appareils tels que des sondes permettant d'espionner les autres joueurs ou des transporteur pour acheminer vos ressources à travers la porte",
        'usage' => "!craft list\n!craft queue\n!craft [id/slug] [quantité]"
    ],
    'defence' => [
        'description' => "Permet de lister et construire des défenses pour protéger vos colonies",
        'usage' => "!defence list\n!defence queue\n!defence [id/slug] [quantité]"
    ],
    'galaxy' => [
        'description' => "Affiche une vue de la galaxie"
                        ."\nVotre zone de vision dépend de la technologie Informatique et Communication."
                        ."\nVision: 2^Lvl systèmes visibles autour de vous."
                        ."\nExemples: \nLvl 0: vous voyez votre système solaire."
                        ."\nLvl 3: Vous voyez 8 systèmes autour du votre"
                        ."\nLvl 8: Accès à la vision des autres galaxies",
        'usage' => '!galaxy'
    ],
    'alliance' => [
        'description' => "Vous permet de créer ou gérer votre alliance.",
        'usage' => "!alliance list\n".
                    "!alliance create [Tag] [Name]\n".
                    "!alliance set internal_description [Description]\n".
                    "!alliance set external_description [Description]\n".
                    "!alliance set leader [@mention]\n".
                    "!alliance set recruitement [on/off]\n".
                    "!alliance role list\n".
                    "!alliance role [role] set [paramètre] [valeur/on/off]\n".
                    "!alliance [invite/promote/demote/kick] [@mention]\n".
                    "!alliance leave\n".
                    "!alliance disband\n".
                    "!alliance upgrade\n"
    ],
    'stargate' => [
        'description' => "Accès à la porte des étoiles de votre colonie\nPermet de partir explorer d'autres planètes afin d'obtenir informations et ressources, d'espionner ou commercer avec les autres joueur voir de les attaquer",
        'usage' => "!stargate explore [coordonées]\n".
                    "!stargate colonize [coordonées]\n".
                    "!stargate move [NuméroDeColonie] [Res1] [Qté1]\n".
                    "!stargate move [coordonées] [Res1] [Qté1]\n".
                    "!stargate spy [coordonées]\n".
                    "!stargate attack [coordonées] military [Qté] [Unit1] [Qté1]"
    ],
    'shipyard' => [
        'description' => "Permet de construire des vaisseaux spatiaux ou d'établir de nouveaux plans personnalisés".
                        "\n\nPour en savoir plus sur la création d'un modèle personnalisé, consultez `!shipyard create`",
        'usage' => "!shipyard [Slug] [Quantité]\n".
                    "!shipyard queue\n".
                    "!shipyard parts\n".
                    "!shipyard create [blueprint] [...Composants]\n".
                    "!shipyard rename [oldSlug] [Nouveau nom]\n".
                    "!shipyard remove [Slug]\n"
    ],
    'fleet' => [
        'description' => "Centre de contrôle des flottes\nIndique les flottes en cours et vous permet de donner des ordres de mission à vos vaisseaux à quai",
        'usage' =>  "!fleet \n".
                    "**order** (`!fleet order [id] return`)\n".
                    //"**explore** (`!fleet explore [coordonées]`)\n".
                    //"**colonize** (`!fleet colonize [coordonées]`)\n".
                    "**base** (`!fleet base [NuméroDeColonie] [Vaisseaux] [Nombre] [Ressource] [Qté]`)\n".
                    "**transport** (`!fleet transport [coordonées] [Vaisseaux] [Nombre] [Ressource] [Qté]`)\n".
                    "**spy** (`!fleet spy [coordonées]`)\n".
                    "**attack** (`!fleet attack [coordonées] [Vaisseaux] [Qté]`)\n".
                    "**scavenge** (`!fleet scavenge [Recycleurs] [Qté]`)\n".
                    "**history** (`!fleet history`)"
    ],
    'infos' => [
        'description' => 'Affiche les informations sur Stargate Bot.',
        'usage' => '!infos'
    ],
    'invite' => [
        'description' => "Affiche le lien permettant d'inviter Stargate sur votre serveur.",
        'usage' => '!invite'
    ],
    'lang' => [
        'description' => 'Donne la possibilité de changer de langue.',
        'usage' => '!lang [fr/en]'
    ],
    'captcha' => [
        'description' => 'Permet de renvoyer le lien du captcha en cas de besoin.',
        'usage' => '!captcha'
    ],
    'ping' => [
        'description' => 'Indique la latence de Stargate Bot.',
        'usage' => '!ping'
    ],
    'profile' => [
        'description' => "Affiche les informations de votre profile tel que vote langue, nombre de vote,..."
                        ."\nPermet également de configurer la réception de notifications lors de la fin de construction/recherche",
        'usage' => "!profile\n!profile notification [on/off]\n!profile vacation"
    ],
    'premium' => [
        'description' => "Si vous désirez apporter votre soutien au bot, vous pouvez acheter un premium via ce lien: **TO BE DEFINED**"
                        ."\nUne fois acheté, vous pouvez également utiliser ou offrir un premium\n\nAvantage du premium:\n".
                        "=> +25% de production des ressources basiques\n".
                        "=> Possibilité de renommer vos colonies\n".
                        "=> Accès à la commande `!empire`",
        'usage' => "!premium\n!premium use\n!premium give @mention"
    ],
    'empire' => [
        'description' => "Vous permet d'afficher une vue d'ensemble de vos colonies, réclamer leur ressources / vérifier la fin de vos building/research/craft/defence en une commande",
        'usage' => "!empire\n!empire fleet\n!empire artifacts"
    ],
    'start' => [
        'description' => "Créer votre profile Stargate afin de commencer votre aventure.",
        'usage' => '!start'
    ],
    'top' => [
        'description' => "Indique les meilleurs joueur par catégories.\n1 point = 1k ressources dépensées",
        'usage' =>  "!top [general/building/research/craft/defence/military]\n!top [general/building/research/craft/defence/military] alliance"
    ],
    'trade'=> [
        'description' => "Liste les trades actifs.\nAfficher le détail d'un trade via `!trade [id]´\nClôturer un trade en cours avant la date de fin avec `!trade [id] close´\nDemander le prolongemnet d'un trade avec `!trade [id] extend´",
        'usage' =>  "!trade list\n".
                    "!trade [ID]\n".
                    "!trade [ID] close\n".
                    "!trade [ID] extend\n"
    ],
    'uptime' => [
        'description' => "Indique la durée depuis laquelle le bot est en ligne.",
        'usage' => '!uptime'
    ],
    'vote' => [
        'description' => "Si vous appréciez Stargate, vous pouvez voter pour ce bot avec le lien derrière cette commande.",
        'usage' => "!vote\n!vote use"
    ],
    'daily' => [
        'description' => "Récompense quotidienne",
        'usage' => '!daily'
    ],
    'hourly' => [
        'description' => "Récompense horaire",
        'usage' => '!hourly'
    ],
];
