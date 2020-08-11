<?php
//HELP FR
return [
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
        'usage' => "!build\n!build [id/slug]\n!build [id/slug] confirm"
    ],
    'research' => [
        'description' => "Permet de lister les technologies disponible, d'afficher le détails d'une technologie ou encore rechercher/upgrade une technologie.",
        'usage' => "!research\n!research [id/slug]\n!research [id/slug] confirm"
    ],
    'colony' => [
        'description' => 'Affiche les informations sur votre colonie (Ressources, Bâtiments, Production, ... ).',
        'usage' => '!colony'
    ],
    'craft' => [
        'description' => "Construit des appareils tel que des sondes permettant d'espionner les autres joueurs ou des transporteur pour acheminer vos ressources à travers la porte",
        'usage' => "!craft list\n!craft queue\n!craft [id/slug] [quantité]"
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
    'stargate' => [
        'description' => "Accès à la porte des étoiles de votre colonie\nPermet de partir explorer d'autres planètes afin d'obtenir informations et ressources, d'espionner ou commercer avec les autres joueur voir de les attaquer",
        'usage' => "!stargate\n!stargate explore [coordonées]\n!stargate spy [coordonées]\n!stargate trade [coordonées] [Ress1] [Qty1]"
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
    'ping' => [
        'description' => 'Indique la latence de Stargate Bot.',
        'usage' => '!ping'
    ],
    'profile' => [
        'description' => "Affiche les informations de votre profile tel que vote langue, nombre de vote, colonies,..."
                        ."\nPermet également de configurer la réception de notifications lors de la fin de construction/recherche",
        'usage' => "!profile\n!profile notification [on/off]"
    ],
    'refresh' => [
        'description' => '[Commande temporaire] Permet de forcer le recalcul de votre production.',
        'usage' => '!refresh'
    ],
    'start' => [
        'description' => "Créer votre profile Stargate afin de commencer votre aventure.",
        'usage' => '!start'
    ],
    'top' => [
        'description' => "Indique les meilleurs joueur par catégories.\n1 point = 1k ressources dépensées",
        'usage' => "!top [general/building/research/military]"
    ],
    'uptime' => [
        'description' => "Indique la durée depuis laquelle le bot est en ligne.",
        'usage' => '!uptime'
    ],
    'vote' => [
        'description' => "Permet de voter pour Stargate si vous apprécier le bot.",
        'usage' => '!vote'
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