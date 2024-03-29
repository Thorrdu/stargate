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
        'description' => "Permet de créer, lister et supprimer des rappels envoyés en dm.\nIl est également possible de consulter l'historique des dm reçus.",
        'usage' => ":prefixreminder [temps] [motif]\nreminder list\n:prefixreminder 1h20m5s va dormir\n:prefixreminder history\n:prefixreminder history [id]\n:prefixreminder remove [id]"
    ],
    'build' => [
        'description' => "Permet de lister les bâtiments disponible, d'afficher le détails d'un bâtiment ou encore construire/upgrade un bâtiment."
                        ."\nAnnuler une construction fait perdre 25% des ressources investies.",
        'usage' => ":prefixbuild\n:prefixbuild [id/slug]\n:prefixbuild [id/slug] confirm\n:prefixbuild queue\n:prefixbuild [id/slug] remove\n:prefixbuild cancel"
    ],
    'research' => [
        'description' => "Permet de lister les technologies disponible, d'afficher le détails d'une technologie ou encore rechercher/upgrade une technologie."
                        ."\nAnnuler une recherche fait perdre 25% des ressources investies.",
        'usage' => ":prefixresearch\n:prefixresearch [id/slug]\n:prefixresearch [id/slug] confirm\n:prefixresearch cancel"
    ],
    'colony' => [
        'description' => 'Affiche les informations essentielles sur votre colonie (Ressources, Bâtiments, Production, ... ) et permet de changer de colonie.',
        'usage' => ":prefixcolony\n:prefixcolony list\n:prefixcolony switch [numéro]\n:prefixcolony remove [numéro]\n:prefixcolony reroll\n:prefixcolony rename [nouveau nom]"
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
    'news' => [
        'description' => "Les BFM Galaxic News permettent de se tenir au courant des activités spéciales se tenant au sein de l'univers.\n".
                         "La ligne éditoriale contient des informations tel que l'arrivée de nouveaux empire ou encore le suivi des échaufourrés entre empires.\n".
                         "Une fois les scanners longue portée rétablis, le suivi des flottes de pirates pourra également être assuré.",
        'usage' => ':prefixnews'
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
    'flex' => [
        'description' => "Vous permet de flex vos ressources aux autres joueurs.",
        'usage' => ":prefixflex [resource] [quantité/all]"
    ],
    'dakara' => [
        'description' => "L'impulsion engrangée par la super-arme de Dakara voyageant dans le sub-espace et se répand jusqu'à l'orbite d'une planète, vous pouvez causer des dégâts instantannés à l'ensemble du système défensif d'une colonie adverse et ce, même si sa porte des étoiles est enterrée.".
                        "\n\nLes dégâts causées s'élèvent à 10% des défenses et 1h de production de militaires par niveau de différence entre la votre et celle de votre adversaire. (Taux de destruction maximal: 30%)".
                        "\nLa portée de base de votre super-arme est de 2 système et double à chaque niveau (2 systèmes ^ niveau). Chaque 128 systèmes de portée, vous accédez à une galaxie supplémentaire.".

                        "\n\nUne utilisation de la super-arme de Dakara compte comme une attaque effectuée par la porte dans votre cycle d'attaque journalier.",
        'usage' => ':prefixdakara [Coordonnées]'
    ],
    'stargate' => [
        'description' => "Accès à la porte des étoiles de votre colonie\nPermet de partir explorer d'autres planètes afin d'obtenir informations et ressources, d'espionner ou commercer avec les autres joueur voir de les attaquer".
                        "**Lvl 5 - Laboratoire de recherche** est requis pour activer la porte vers d'autres planètes..\n".
                        "Cependant, au Lvl 4, les autres joueurs pourront se connecter à votre porte",
        'usage' => ":prefixstargate explore [Coordonnées]\n".
                    ":prefixstargate colonize [Coordonnées]\n".
                    ":prefixstargate move [NuméroDeColonie/Coordonnées] [Res1] [Qté1]\n".
                    ":prefixstargate trade [Coordonnées] [Res1] [Qté1]\n".
                    ":prefixstargate spy [Coordonnées]\n".
                    ":prefixstargate attack [Coordonnées] military [Qté] [Unit1] [Qté1]\n".
                    ":prefixstargate bury"
    ],
    'shipyard' => [
        'description' => "Permet de construire des vaisseaux spatiaux ou d'établir de nouveaux plans personnalisés".
                        "\n\nPour en savoir plus sur la création d'un modèle personnalisé, consultez `:prefixshipyard create`",
        'usage' => ":prefixshipyard [Slug] [Quantité]\n".
                    ":prefixshipyard [Slug] recycle [Quantité]\n".
                    ":prefixshipyard queue\n".
                    ":prefixshipyard parts\n".
                    ":prefixshipyard create [blueprint] [...Composants]\n".
                    ":prefixshipyard rename [oldSlug] [Nouveau nom]\n".
                    ":prefixshipyard remove [Slug]\n"
    ],
    'fleet' => [
        'description' => "Centre de contrôle des flottes\nIndique les flottes en cours et vous permet de donner des ordres de mission à vos vaisseaux à quai.\n\n".
                        "Exemple pour transporter 100 iron et 50 gold vers la colonie située en [1;1;1] avec 3 vaisseaux nommés 'MonVaisseau' (slug:monvaisseau):".
                        "\n`!fleet transport 1;1;1 monvaisseau 3 iron 100 gold 50`".
                        "\n\nParamètres optionels:".
                        "\nspeed [entre 10 et 100] => réduit la vitesse et consommation de votre flotte au pourcentage souhaité.".
                        "\nboost => consomme 05 E2PZ par vaisseau et octroie un bonus de +10% bouclier et +20% vitesse",
        'usage' =>  ":prefixfleet \n".
                    "**order** (`:prefixfleet order [id] return`)\n".
                    //"**explore** (`:prefixfleet explore [Coordonnées]`)\n".
                    //"**colonize** (`:prefixfleet colonize [Coordonnées]`)\n".
                    "**base** (`:prefixfleet base [NuméroDeColonie] [Vaisseaux] [Nombre] [Ressource] [Qté]`)\n".
                    "**transport** (`:prefixfleet transport [Coordonnées] [Vaisseaux] [Nombre] [Ressource] [Qté]`)\n".
                    "**spy** (`:prefixfleet spy [Coordonnées]`)\n".
                    "**attack** (`:prefixfleet attack [Coordonnées] [Vaisseaux] [Qté]`)\n".
                    "**scavenge** (`:prefixfleet scavenge [Coordonnées] [Recycleurs] [Qté]`)\n".
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
        'description' => "Affiche les informations de votre profile tel que vote langue, nombre de vote,...".
                        "\nPermet également de configurer la réception de notifications lors de la fin de construction/recherche ou quand votre vote est de nouveau disponible.".
                        "\nSi vous le souhaitez, vous pouvez cacher les coordonnées lors de l'affichage de `!colony`".
                        "\nCette commande permet également d'activer ou désactiver le mode vacance.",
        'usage' => ":prefixprofile\n:prefixprofile notification [on/off]\n:prefixprofile hide [on/off]\n:prefixprofile vacation"
    ],
    'premium' => [
        'description' => "Si vous désirez apporter votre soutien au bot, vous pouvez acheter un premium via ce lien: **[Utip](https://utip.io/thorrdu)** (Pour payer par Paypal ou Paysafecard, contactez Thorrdu en DM)\n".
                        "Prix: 5 Euros = 1 Mois / 5 Premium achetés = 1 Premium offert.\n".
                        "\nUne fois acheté, vous pouvez également utiliser ou offrir un premium\n\nAvantage du premium:\n".
                        "=> +30% de production\n".
                        "=> -20% de temps de construction/recherche\n".
                        "=> Possibilité de renommer vos colonies\n".
                        "=> Accès à la commande `:prefixempire\n`".
                        "=> Construction de bâtiments à la chaîne\n",
        'usage' => ":prefixpremium\n:prefixpremium use\n:prefixpremium give @mention\n:prefixpremium give @mention [quantité]"
    ],
    'empire' => [
        'description' => "Vous permet d'afficher une vue d'ensemble de vos colonies, réclamer leur ressources / vérifier la fin de vos building/research/craft/defence en une commande",
        'usage' => ":prefixempire\n:prefixempire activities\n:prefixempire production\n:prefixempire buildings\n:prefixempire fleet\n:prefixempire artifacts"
    ],
    'start' => [
        'description' => "Créer votre profile Stargate afin de commencer votre aventure.",
        'usage' => ':prefixstart'
    ],
    'top' => [
        'description' => "Indique les meilleurs joueur par catégories.\n1 point = 1k ressources dépensées",
        'usage' =>  ":prefixtop [general/building/research/craft/military]\n:prefixtop [general/building/research/craft/military] alliance"// /defence
    ],
    'trade'=> [
        'description' => "Liste les trades actifs.\nAfficher le détail d'un trade via `:prefixtrade [id]`\nClôturer un trade en cours avant la date de fin avec `:prefixtrade [id] close`".
                        "\nInviter un joueur à un pacte commercial avec `:prefixtrade pact <mention>` ou annulez-en un avec `:prefixtrade pact <mention> cancel` ",
        'usage' =>  ":prefixtrade list\n".
                    ":prefixtrade ratio\n".
                    ":prefixtrade [ID]\n".
                    ":prefixtrade [ID] close\n".
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
