<?php
//SHIP FR
return [
    'jumper' => [
        'name' => "Jumper"
    ],
    'deathglider' => [
        'name' => "Planeur de la mort"
    ],
    'teltak' => [
        'name' => "Tel'tak"
    ],
    'alkesh' => [
        'name' => "Al'kesh"
    ],
    'prometheus' => [
        'name' => "Prometheus"
    ],
    'hatak' => [
        'name' => "Ha'tak"
    ],
    'destiny' => [
        'name' => "Destiny"
    ],
    'oriwarship' => [
        'name' => "Vaisseau de guerre Ori"
    ],
    'taurihull' => [
        'name' => "Coque Tau'ri"
    ],
    'goauldhull' => [
        'name' => "Coque Goa'uld"
    ],
    'wraithhull' => [
        'name' => "Coque Wraith"
    ],
    'asgardhull' => [
        'name' => "Coque Asgard"
    ],
    'lanteanhull' => [
        'name' => "Coque lantienne"
    ],
    'reinforcedlanteanhull' => [
        'name' => "Coque Lantienne renforcée"
    ],
    'goauldshield' => [
        'name' => "Bouclier Goa'uld"
    ],
    'asgardshield' => [
        'name' => "Bouclier Asgard"
    ],
    'lantianshield' => [
        'name' => "Bouclier Lantien"
    ],
    'anubishield' => [
        'name' => "Super Bouclier d'Anubis"
    ],
    'atlantisshield' => [
        'name' => "Bouclier d'Atlantis"
    ],
    'combustionreactor' => [
        'name' => "Réacteur à Combustion"
    ],
    'advancedcombustion' => [
        'name' => "Combustion améliorée"
    ],
    'ionreactor' => [
        'name' => "Réacteur à Ion"
    ],
    'fusionreactor' => [
        'name' => "Réacteur à fusion"
    ],
    'wraithreactor' => [
        'name' => "Réacteur Wraith"
    ],
    'goauldreactor' => [
        'name' => "Réacteur Goa'uld"
    ],
    'asgardreactor' => [
        'name' => "Reacteur Asgard"
    ],
    'lantianreactor' => [
        'name' => "Reacteur Lantien"
    ],
    'projectileturret' => [
        'name' => "Tourelle à projectile"
    ],
    'markiiimissile' => [
        'name' => "Missiles MARK III"
    ],
    'lasercannon' => [
        'name' => "Canon laser"
    ],
    'goauldioncannon' => [
        'name' => "Canon à ion Goa'uld"
    ],
    'plasmacannon' => [
        'name' => "Canon à plasma"
    ],
    'plasmabomb' => [
        'name' => "Bombe à plasma"
    ],
    'naqhadriabomb' => [
        'name' => "Bombe au naqahdria"
    ],
    'droncelauncher' => [
        'name' => "Lanceur de drone"
    ],
    'orienergybeam' => [
        'name' => "Rayon à énergie Ori"
    ],
    'hidden' => '-- Vaisseau caché --',
    'unDiscovered' => 'Non découvert',
    "notBuilt" => "Cette colonie ne possède pas de chantier spacial.",
    'buildingStarted' => 'Construction de vaisseau, **:qtyx :name**. Terminé dans **:time**',
    'howTo' => "Construisez un vaisseau avec `!shipyard [slug] [quantité]`\n\n",
    'notYetDiscovered' => "Vous n'avez pas encore découvert ce vaisseau.",
    "unknownShip" => "Vaisseau inconnu",
    "shipList" => "Liste des vaisseaux",
    "genericHowTo" => "TEXTE",
    "shipQueue" => "Liste des vaisseaux en cours",
    "emptyQueue" => "Aucun vaisseau en cours",
    "emptyList" => "Aucun plan de vaisseau disponible. Pour en créer un, rendez-vous dans `!shipyard create`",
    "firePower" => "Armement: **:firepower**",
    "shield" => "Bouclier: **:shield**",
    "hull" => "Coque: **:hull**",
    "capacity" => "Capacité: **:capacity**",
    'usedCapacity' => "Capacité utilisée: **:usedCapacity**",
    "crew" => "Equipage: **:crew**",
    "speed" => "Vitesse: **:speed**",
    'shipNameChanged' => 'Votre vaisseau est désormais connu sous le nom de: **:name** (Slug: `:slug`)',
    'modelCreation' => 'Création d\'un nouveau modèle de vaisseau',
    'newModelCreated' => "Félicitation, vous possédez désormais un nouveau plan de vaisseau, le `:modelName`!".
                        "\nPour le renommer, utilisez `!ship rename :modelSlug [Le nouveau nom]`",
    'missingComponement' => 'Composants manquants. Vous devez au moins spécifier: Un plan de base, une arme, une coque et un réacteur.',
    'missingFuelStorage' => 'Emplacement manquant pour stocker du carburant. Vous devez au moins laisser une capacité de stockage de 100.',
    'unknownShipPart' => 'Composant inconnu: :part.',
    'unknownBlueprint' => 'Plan inconnu: :part.',
    'modelsLimitReached' => 'Nombre de plans de vaisseau personnalisés atteint ( 15 ).',
    'impossibleRemoval' => 'Suppression impossible, vous possédez actuellement des exemplaires de ce vaisseau.',
    'modelRemoved' => 'Modèle supprimé...',
    'customModelTitle' => 'Création d\'un modèle de vaisseau personnalisé',
    'customModelsTutorial' => "Un modèle personnalisé est composé au minimum de:\n".
                              "-Un plan de vaisseau\n".
                              "-Un système d'armement\n".
                              "-Une coque\n".
                              "-Un système de propulsion\n\n".
                              "Exemple de commande permettant de créer un nouveau modèle: \n".
                              "`!ship create jumper projectileturret 1 taurihull 1 combustionreactor 1`\n".
                              "Les composants pouvant servir à la fabrication d'un vaisseau peuvent être consultés via `!shipyard parts`\n",
    'componentList' => 'Liste des composants',
];
